<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Country;
use App\Models\Employer;
use App\Models\User;
use App\Services\SensitiveActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class EmployerController extends Controller
{
    public function index(): View
    {
        $employers = Employer::latest()->paginate(15);
        return view('employers.index', compact('employers'));
    }

    public function create(): View
    {
        $this->authorizeFraManager();

        $countries = Country::orderBy('name')->get();
        return view('employers.create', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeFraManager();

        $this->validateCustomFields($request, 'Employer');

        $validated = $request->validate([
            'company_no'    => 'nullable|string|max:50',
            'name'          => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact'       => 'nullable|string|max:100',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'country_id'    => 'nullable|exists:countries,id',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }

        $employer = Employer::create($validated);

        $employer->syncCustomFields($request->all());

        // Auto-create employer login user if email is set and no user exists
        if ($employer->email && ! User::where('email', $employer->email)->exists()) {
            $password = $request->filled('password') ? $request->password : Str::random(12);
            User::create([
                'name'        => $employer->contact_person ?? $employer->name,
                'email'       => $employer->email,
                'password'    => bcrypt($password),
                'user_type'   => 'employer',
                'employer_id' => $employer->id,
                'agency_id'   => $employer->agency_id,
            ]);
        }

        return redirect()->route('employers.index')
            ->with('success', 'Employer created successfully.');
    }

    public function show(Employer $employer): View
    {
        $employer->load('country', 'jobPositions');
        return view('employers.show', compact('employer'));
    }

    public function edit(Employer $employer): View
    {
        $countries = Country::orderBy('name')->get();
        return view('employers.edit', compact('employer', 'countries'));
    }

    public function update(Request $request, Employer $employer): RedirectResponse
    {
        $this->validateCustomFields($request, 'Employer');

        $validated = $request->validate([
            'company_no'    => 'nullable|string|max:50',
            'name'          => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact'       => 'nullable|string|max:100',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'country_id'    => 'nullable|exists:countries,id',
            'status'        => 'nullable|string|max:50',
        ]);

        $employer->update($validated);

        $employer->syncCustomFields($request->all());

        return redirect()->route('employers.index')
            ->with('success', 'Employer updated successfully.');
    }

    public function destroy(Employer $employer): RedirectResponse
    {
        // (Mjolnir card "FRA Module") Only Admin / Super Admin may delete an FRA.
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Only an Admin can delete an FRA.');
        }

        $employer->delete();

        return redirect()->route('employers.index')
            ->with('success', 'Employer deleted successfully.');
    }

    /**
     * (Mjolnir card "FRA Module") Adding an FRA (single or bulk) is reserved
     * for Admin and Accounting. Receptionist (staff), Processing, Paralegal,
     * Branch and Operation users may only view the list.
     */
    protected function authorizeFraManager(): void
    {
        if (auth()->user()?->isRestOfAccount()) {
            abort(403, 'Your account cannot add FRAs.');
        }
    }

    // ---------------------------------------------------------------------
    // Bulk CSV upload (mirrors the applicant bulk upload flow)
    // ---------------------------------------------------------------------

    /**
     * Bulk upload page: explains the flow, offers the template download and
     * the CSV upload form.
     */
    public function bulkUpload(): View
    {
        $this->authorizeFraManager();

        $countries = Country::orderBy('name')->get(['id', 'name']);

        return view('employers.bulk', compact('countries'));
    }

    /**
     * Download the bulk import CSV template. Includes two SAMPLE rows (Active
     * and Inactive) so users can follow the exact format.
     */
    public function bulkTemplate()
    {
        $this->authorizeFraManager();

        $headers = [
            'Company Name', 'Company No.', 'Status', 'Contact Person',
            'Contact', 'Email', 'Address', 'Country',
        ];

        $sampleRows = [
            [
                'Gulf Horizon Manpower Inc.', 'EMP-001', 'Active',
                'Juan Dela Cruz', '09171234567', 'gulfhorizon@example.com',
                '123 Trade St, Riyadh, KSA', 'Saudi Arabia',
            ],
            [
                'Al Noor Recruitment Co.', 'EMP-002', 'Inactive',
                'Maria Santos', '09179876543', 'alnoor@example.com',
                '456 Corniche Rd, Dubai, UAE', 'UAE',
            ],
        ];

        $callback = function () use ($headers, $sampleRows) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=employers_bulk_template.csv',
        ]);
    }

    /**
     * Parse the uploaded CSV, validate EVERY row first, and only then insert.
     * Any row error aborts the whole import and reports each bad row (with its
     * CSV line number) so the file can be fixed and re-uploaded.
     *
     * Matching is deliberately case/space-insensitive for the Country and
     * Status columns. Company Name is the only required column; rows with a
     * name get a login user auto-created when an email is present (mirrors the
     * single-create form).
     */
    public function bulkImport(Request $request): RedirectResponse
    {
        $this->authorizeFraManager();

        $agencyId = $this->resolveAgencyId();
        if (! $agencyId) {
            return back()->withErrors(['csv_file' => 'No agency context. Please log in with an agency account to import FRAs.']);
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('csv_file');
        $rows = $this->parseBulkCsv($file->getRealPath());
        if (is_string($rows)) {
            // parse error (bad file / missing required headers / too many rows)
            return back()->withErrors(['csv_file' => $rows]);
        }

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        $countryMap = [];
        foreach (Country::all() as $country) {
            $key = $norm($country->name);
            if ($key !== '' && ! array_key_exists($key, $countryMap)) {
                $countryMap[$key] = $country->id;
            }
        }

        $errors = [];
        $validatedRows = [];
        $emailsUsed = []; // emails already assigned a login user in this file

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            $name = trim((string) ($d['name'] ?? ''));
            $companyNo = trim((string) ($d['company_no'] ?? ''));
            $status = trim((string) ($d['status'] ?? ''));
            $contactPerson = trim((string) ($d['contact_person'] ?? ''));
            $contact = trim((string) ($d['contact'] ?? ''));
            $email = mb_strtolower(trim((string) ($d['email'] ?? '')));
            $address = trim((string) ($d['address'] ?? ''));
            $countryRaw = trim((string) ($d['country'] ?? ''));

            // Note: fully-blank lines were already skipped by the parser.
            // A row that has ANY data must have a Company Name.

            // ---- Plain fields ----
            if ($name === '') {
                $rowErrors[] = 'Company Name is required.';
            } elseif (mb_strlen($name) > 255) {
                $rowErrors[] = 'Company Name is too long (max 255).';
            }
            if (mb_strlen($companyNo) > 50) {
                $rowErrors[] = 'Company No. is too long (max 50).';
            }
            if (mb_strlen($contactPerson) > 255) {
                $rowErrors[] = 'Contact Person is too long (max 255).';
            }
            if (mb_strlen($contact) > 100) {
                $rowErrors[] = 'Contact is too long (max 100).';
            }
            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Email '{$email}' is not a valid email address.";
            } elseif (mb_strlen($email) > 255) {
                $rowErrors[] = 'Email is too long (max 255).';
            }

            // ---- Status (Active / Inactive; case/space-insensitive; default Active) ----
            $statusValue = 'active';
            if ($status !== '') {
                $statusKey = $norm($status);
                if ($statusKey === 'active' || $statusKey === '1') {
                    $statusValue = 'active';
                } elseif ($statusKey === 'inactive' || $statusKey === '0') {
                    $statusValue = 'inactive';
                } else {
                    $rowErrors[] = "Status '{$status}' not found. Use 'Active' or 'Inactive' (or leave blank for Active).";
                }
            }

            // ---- Country (lookup by name; optional) ----
            $countryId = null;
            if ($countryRaw !== '') {
                $key = $norm($countryRaw);
                if (isset($countryMap[$key])) {
                    $countryId = $countryMap[$key];
                } else {
                    $available = collect(array_keys($countryMap))->take(6)->map(fn ($k) => ucwords($k))->implode(', ');
                    $rowErrors[] = "Country '{$countryRaw}' not found.".($available !== '' ? " Available: {$available}" : '');
                }
            }

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            $validatedRows[] = [
                'name' => $name,
                'company_no' => $companyNo !== '' ? $companyNo : null,
                'status' => $statusValue,
                'contact_person' => $contactPerson !== '' ? $contactPerson : null,
                'contact' => $contact !== '' ? $contact : null,
                'email' => $email !== '' ? $email : null,
                'address' => $address !== '' ? $address : null,
                'country_id' => $countryId,
            ];

            if ($email !== '' && ! in_array($email, $emailsUsed, true)) {
                $emailsUsed[] = $email;
            }
        }

        if (count($validatedRows) > 2000) {
            return back()->withErrors(['csv_file' => 'Too many rows: the file has more than 2,000 FRA rows. Split it into smaller files.']);
        }

        if (! empty($errors)) {
            return back()->with('bulk_errors', $errors)->withInput();
        }

        // ---- All rows valid: insert in one transaction ----
        $count = count($validatedRows);
        DB::transaction(function () use ($validatedRows, $emailsUsed, $agencyId) {
            foreach ($validatedRows as $data) {
                $data['agency_id'] = $agencyId;

                $employer = Employer::create($data);

                // Auto-create FRA login user if email is set and not already
                // used (mirrors the single-create form). Duplicate emails in
                // the same file only get ONE login account.
                $email = $data['email'] ?? null;
                if ($email && in_array($email, $emailsUsed, true)
                    && ! User::where('email', $email)->exists()) {
                    User::create([
                        'name'        => $data['contact_person'] ?? $data['name'],
                        'email'       => $email,
                        'password'    => bcrypt(Str::random(12)),
                        'user_type'   => 'employer',
                        'employer_id' => $employer->id,
                        'agency_id'   => $agencyId,
                    ]);
                    $key = array_search($email, $emailsUsed, true);
                    if ($key !== false) {
                        unset($emailsUsed[$key]); // only the first row creates the login
                    }
                }
            }
        });

        SensitiveActionLogger::log(
            'bulk_employer_import',
            subject: null,
            description: auth()->user()->name." bulk-imported {$count} FRA(s) via CSV.",
            metadata: ['count' => $count],
            agencyId: $agencyId,
        );

        return redirect()->route('employers.index')
            ->with('success', "Bulk upload complete: {$count} FRA(s) imported from CSV.");
    }

    /**
     * Read a CSV file into rows keyed by canonical field name.
     *
     * Tolerant parser aimed at real-world Excel round-trips:
     *  - strips UTF-8 BOM, decodes UTF-16 (LE/BE), falls back to Windows-1252
     *  - auto-detects delimiter (comma / semicolon / tab)
     *  - does NOT assume the header is the very first line: leading blank lines
     *    or a stray title row are skipped until a row containing the required
     *    "Company Name" header is found
     *  - unknown extra columns are ignored
     *
     * @return array|string array of ['line' => int, 'data' => [field => value]] or an error message
     */
    private function parseBulkCsv(string $path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return 'Could not read the uploaded file.';
        }

        // ---- Encoding normalization (Excel writes all of these) ----
        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3); // UTF-8 BOM
        }
        if ($raw !== '' && ! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        }
        if (trim($raw) === '') {
            return 'The file is empty.';
        }

        // Normalize line endings — Excel for Mac / some exports use a lone CR,
        // which fgetcsv would otherwise read as a single giant row (so the
        // header row is never seen). (Fixed 2026-10-07)
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        // ---- Try each plausible delimiter; the first one that yields a valid
        // header row wins. ----
        $lastError = null;
        $headerFoundError = null;
        foreach ([',', ';', "\t"] as $delimiter) {
            $result = $this->parseBulkCsvWithDelimiter($raw, $delimiter);
            if (is_array($result)) {
                return $result;
            }

            // A delimiter that *did* recognize the header row but then failed
            // for a concrete reason (no data rows / too many rows) is far more
            // useful than the generic "no header row" from a wrong delimiter —
            // don't let the wrong delimiter's message win. (Fixed 2026-10-07)
            if (! str_starts_with($result, 'No header row found')) {
                $headerFoundError ??= $result;
            }

            $lastError = $result;
        }

        return $headerFoundError ?? $lastError;
    }

    /**
     * @return array|string parsed rows, or an error message for this delimiter
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

        // ---- Find the header row: skip blank lines and stray title lines ----
        $headerIndex = null;
        $fieldByIndex = [];
        $scanLimit = min(count($parsedRows), 30);
        for ($i = 0; $i < $scanLimit; $i++) {
            $cells = $parsedRows[$i];
            if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                continue; // blank line
            }
            $candidate = [];
            foreach ($cells as $idx => $header) {
                $field = $aliases[$this->normKey($header)] ?? null;
                if ($field && ! in_array($field, $candidate, true)) {
                    $candidate[$idx] = $field;
                }
            }
            $fields = array_values($candidate);
            // A row only counts as the header when it names the required column.
            if (in_array('name', $fields, true)) {
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

            return 'No header row found. The file must have a header row containing a "Company Name" column (use the downloaded template and keep its first row unchanged). What the parser saw first: "'.$preview.'". If you added a title or blank line above the header, remove it, or just re-download the template and fill it in.';
        }

        // ---- Data rows = everything after the header row ----
        $rows = [];
        $line = $headerIndex + 1; // physical CSV line of the header
        $dataLines = 0;
        for ($i = $headerIndex + 1; $i < count($parsedRows); $i++) {
            $line++;
            $cells = $parsedRows[$i];
            $data = [];
            foreach ($fieldByIndex as $idx => $field) {
                $data[$field] = trim((string) ($cells[$idx] ?? ''));
            }
            if (implode('', array_values($data)) === '') {
                continue; // skip blank lines
            }
            $dataLines++;
            if ($dataLines > 2000) {
                return 'Too many rows: the file exceeds 2,000 FRA rows. Split it into smaller files.';
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }

        if (empty($rows)) {
            return 'The file has no data rows below the header.';
        }

        return $rows;
    }

    /**
     * Map normalized header labels to canonical employer fields.
     *
     * @return array<string, string>
     */
    private function bulkHeaderAliases(): array
    {
        return [
            'name' => 'name',
            'company' => 'name',
            'companyname' => 'name',
            'employer' => 'name',
            'fra' => 'name',
            'agencyname' => 'name',
            'companyno' => 'company_no',
            'companynumber' => 'company_no',
            'employerno' => 'company_no',
            'status' => 'status',
            'contactperson' => 'contact_person',
            'contact' => 'contact',
            'phone' => 'contact',
            'phonenumber' => 'contact',
            'telephone' => 'contact',
            'email' => 'email',
            'emailaddress' => 'email',
            'address' => 'address',
            'officeaddress' => 'address',
            'country' => 'country',
            'countryname' => 'country',
            'location' => 'country',
        ];
    }

    /**
     * Normalize a header label: lowercase, strip non-alphanumerics.
     */
    private function normKey($value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $value));
    }

    public function soa(Employer $employer): View
    {
        $bills = Bill::with('payments')
            ->where('employer_id', $employer->id)
            ->latest()
            ->get();

        $totalBilled = $bills->sum('employer_cost');
        $totalPaid = $bills->flatMap->payments->sum('amount');
        $balance = $totalBilled - $totalPaid;

        return view('employers.soa', compact('employer', 'bills', 'totalBilled', 'totalPaid', 'balance'));
    }
}
