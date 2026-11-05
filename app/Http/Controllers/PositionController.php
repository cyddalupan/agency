<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class PositionController extends Controller
{
    private const MAX_ROWS = 2000;

    public function index(): View
    {
        $positions = Position::orderBy('name')->paginate(print_per_page(20));

        return view('positions.index', compact('positions'));
    }

    public function create(): View
    {
        return view('positions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name',
            'description' => 'nullable|string|max:255',
            'category'    => 'nullable|string|in:household,skilled',
        ]);

        $validated['category'] = $validated['category'] ?? Position::CATEGORY_SKILLED;

        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position added successfully.');
    }

    public function edit(Position $position): View
    {
        return view('positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:positions,name,' . $position->id,
            'description' => 'nullable|string|max:255',
            'category'    => 'nullable|string|in:household,skilled',
        ]);

        if (array_key_exists('category', $validated) && $validated['category'] === null) {
            unset($validated['category']);
        }

        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        if ($position->applicants()->exists() || $position->jobPositions()->exists()) {
            return back()->withErrors([
                'position' => "Cannot delete '{$position->name}' — it is still used by applicants or job positions.",
            ]);
        }

        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Position deleted successfully.');
    }

    // ---------------------------------------------------------------------
    // CSV export / bulk import (mirrors the applicant & FRA bulk flows)
    // ---------------------------------------------------------------------

    /**
     * Download every position as CSV (Name, Description, Category).
     */
    public function export()
    {
        $positions = Position::orderBy('name')->get(['name', 'description', 'category']);

        $callback = function () use ($positions) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Name', 'Description', 'Category']);

            foreach ($positions as $position) {
                fputcsv($file, [
                    $position->name,
                    $position->description ?? '',
                    $position->category ?? Position::CATEGORY_SKILLED,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=positions.csv',
        ]);
    }

    /**
     * Bulk upload page: template download + CSV upload form.
     */
    public function bulkUpload(): View
    {
        return view('positions.bulk');
    }

    /**
     * Download the import template with two sample rows (one per category).
     */
    public function bulkTemplate()
    {
        $headers = ['Name', 'Description', 'Category'];

        $sampleRows = [
            ['Domestic Helper', 'Household cleaning and childcare', 'household'],
            ['Welder', '6G pipe welding for oil & gas projects', 'skilled'],
        ];

        $callback = function () use ($headers, $sampleRows) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);

            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=positions_bulk_template.csv',
        ]);
    }

    /**
     * Validate every row first, then insert in one transaction.
     */
    public function bulkImport(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->parseBulkCsv($request->file('csv_file')->getRealPath());
        if (is_string($rows)) {
            return back()->withErrors(['csv_file' => $rows]);
        }

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        $existing = [];
        foreach (Position::pluck('name') as $name) {
            $existing[$norm($name)] = true;
        }

        $errors = [];
        $validatedRows = [];
        $seen = [];

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            $name = trim((string) ($d['name'] ?? ''));
            $description = trim((string) ($d['description'] ?? ''));
            $categoryRaw = trim((string) ($d['category'] ?? ''));

            if ($name === '') {
                $rowErrors[] = 'Name is required.';
            } elseif (mb_strlen($name) > 255) {
                $rowErrors[] = 'Name is too long (max 255).';
            } else {
                $key = $norm($name);
                if (isset($existing[$key])) {
                    $rowErrors[] = "Position '{$name}' already exists.";
                } elseif (isset($seen[$key])) {
                    $rowErrors[] = "Duplicate position '{$name}' in this file (first on line {$seen[$key]}).";
                }
            }

            if (mb_strlen($description) > 255) {
                $rowErrors[] = 'Description is too long (max 255).';
            }

            $category = Position::CATEGORY_SKILLED;
            if ($categoryRaw !== '') {
                $categoryKey = $norm($categoryRaw);
                if ($categoryKey === Position::CATEGORY_HOUSEHOLD) {
                    $category = Position::CATEGORY_HOUSEHOLD;
                } elseif ($categoryKey === Position::CATEGORY_SKILLED) {
                    $category = Position::CATEGORY_SKILLED;
                } else {
                    $rowErrors[] = "Category '{$categoryRaw}' not found. Use 'household' or 'skilled' (or leave blank for skilled).";
                }
            }

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            $seen[$norm($name)] = $line;
            $validatedRows[] = [
                'name'        => $name,
                'description' => $description !== '' ? $description : null,
                'category'    => $category,
            ];
        }

        if (! empty($errors)) {
            return back()->with('bulk_errors', $errors)->withInput();
        }

        DB::transaction(function () use ($validatedRows) {
            foreach ($validatedRows as $data) {
                Position::create($data);
            }
        });

        $count = count($validatedRows);

        return redirect()->route('positions.index')
            ->with('success', "Bulk upload complete: {$count} position(s) imported from CSV.");
    }

    /**
     * Tolerant CSV parser: BOM/UTF-16, auto delimiter, header scan, blank rows.
     *
     * @return array|string
     */
    private function parseBulkCsv(string $path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return 'Could not read the uploaded file.';
        }

        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        if ($raw !== '' && ! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        }
        if (trim($raw) === '') {
            return 'The file is empty.';
        }

        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $lastError = null;
        $headerFoundError = null;
        foreach ([',', ';', "\t"] as $delimiter) {
            $result = $this->parseBulkCsvWithDelimiter($raw, $delimiter);
            if (is_array($result)) {
                return $result;
            }
            if (! str_starts_with($result, 'No header row found')) {
                $headerFoundError ??= $result;
            }
            $lastError = $result;
        }

        return $headerFoundError ?? $lastError;
    }

    /**
     * @return array|string
     */
    private function parseBulkCsvWithDelimiter(string $raw, string $delimiter)
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);

        $parsedRows = [];
        while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
            $parsedRows[] = $cells;
        }
        fclose($handle);

        if (empty($parsedRows)) {
            return 'The file has no readable rows.';
        }

        $aliases = $this->bulkHeaderAliases();

        $headerIndex = null;
        $fieldByIndex = [];
        $scanLimit = min(count($parsedRows), 30);
        for ($i = 0; $i < $scanLimit; $i++) {
            $cells = $parsedRows[$i];
            if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                continue;
            }
            $candidate = [];
            foreach ($cells as $idx => $header) {
                $field = $aliases[$this->normKey($header)] ?? null;
                if ($field && ! in_array($field, $candidate, true)) {
                    $candidate[$idx] = $field;
                }
            }
            if (in_array('name', array_values($candidate), true)) {
                $headerIndex = $i;
                $fieldByIndex = $candidate;
                break;
            }
        }

        if ($headerIndex === null) {
            $firstNonBlank = null;
            foreach (array_slice($parsedRows, 0, 5) as $cells) {
                if (! empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    $firstNonBlank = implode(' | ', array_map(fn ($c) => trim((string) $c), $cells));
                    break;
                }
            }
            $preview = mb_substr($firstNonBlank ?? '(empty file)', 0, 160);

            return 'No header row found. The file must have a header row containing a "Name" column (use the downloaded template and keep its first row unchanged). What the parser saw first: "'.$preview.'".';
        }

        $rows = [];
        $line = $headerIndex + 1;
        $dataLines = 0;
        for ($i = $headerIndex + 1; $i < count($parsedRows); $i++) {
            $line++;
            $cells = $parsedRows[$i];
            $data = [];
            foreach ($fieldByIndex as $idx => $field) {
                $data[$field] = trim((string) ($cells[$idx] ?? ''));
            }
            if (implode('', array_values($data)) === '') {
                continue;
            }
            $dataLines++;
            if ($dataLines > self::MAX_ROWS) {
                return 'Too many rows: the file exceeds 2,000 position rows. Split it into smaller files.';
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }

        if (empty($rows)) {
            return 'The file has no data rows below the header.';
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function bulkHeaderAliases(): array
    {
        return [
            'name'             => 'name',
            'position'         => 'name',
            'positionname'     => 'name',
            'job'              => 'name',
            'jobtitle'         => 'name',
            'title'            => 'name',
            'description'      => 'description',
            'details'          => 'description',
            'category'         => 'category',
            'type'             => 'category',
            'positioncategory' => 'category',
        ];
    }

    private function normKey($value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $value));
    }
}
