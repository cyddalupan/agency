<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * EMPLOYEE module (Mjolnir card 6af6fe94, 2026-11-13).
 *
 * Only CSDI and FINAS offer this module — enforced in ModuleAccess
 * (EMPLOYEE_AGENCIES) and by employee_enabled() in the views, so the other
 * tenants never see the link and get a 403 if they guess the URL.
 *
 * Every query is scoped to the signed-in user's agency; a record from another
 * agency is a 403 on route-model binding, never a data leak.
 */
class EmployeeController extends Controller
{
    /** Uploaded documents live here, under the agency's own folder. */
    private const DOC_FIELDS = [
        'appointment_document' => 'appointment_document_name',
        'nbi_document'         => 'nbi_document_name',
        'training_document'    => 'training_document_name',
        'qms_document'         => 'qms_document_name',
    ];

    public function index(Request $request): View
    {
        $agencyId = $this->agencyId();

        $query = Employee::where('agency_id', $agencyId);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_no', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('employment_status')) {
            $query->where('employment_status', $status);
        }

        if ($docStatus = $request->query('document_status')) {
            $query->where('document_status', $docStatus);
        }

        if ($branch = $request->query('branch_id')) {
            $query->where('branch_id', $branch);
        }

        $employees = $query->with('branch')
            ->orderBy('full_name')
            ->paginate(print_per_page(25))
            ->withQueryString();

        $branches   = Branch::where('agency_id', $agencyId)->orderBy('name')->get();
        $statuses   = Employee::EMPLOYMENT_STATUSES;
        $docStatuses = Employee::DOCUMENT_STATUSES;

        return view('employees.index', compact('employees', 'branches', 'statuses', 'docStatuses'));
    }

    /** CSV of the filtered list — the client works these in Excel. */
    public function export(Request $request)
    {
        $agencyId = $this->agencyId();

        $query = Employee::where('agency_id', $agencyId);
        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_no', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }
        if ($status = $request->query('employment_status')) {
            $query->where('employment_status', $status);
        }
        if ($docStatus = $request->query('document_status')) {
            $query->where('document_status', $docStatus);
        }
        if ($branch = $request->query('branch_id')) {
            $query->where('branch_id', $branch);
        }

        $rows = $query->with('branch')->orderBy('full_name')->get();

        $headers = [
            'Employee ID', 'Full Name', 'Position / Designation', 'Department',
            'Branch Assignment', 'Employment Status', 'Date Hired',
            'NBI Reference', 'NBI Issued', 'NBI Expiry', 'Document Status',
        ];

        return response()->streamDownload(function () use ($rows, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $e) {
                fputcsv($out, [
                    $e->employee_no,
                    $e->full_name,
                    $e->position,
                    $e->department,
                    $e->branch->name ?? '',
                    $e->employment_status,
                    optional($e->date_hired)->format('Y-m-d'),
                    $e->nbi_reference,
                    optional($e->nbi_issued_at)->format('Y-m-d'),
                    optional($e->nbi_expires_at)->format('Y-m-d'),
                    $e->document_status,
                ]);
            }
            fclose($out);
        }, 'employees-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        return view('employees.form', $this->formData(null));
    }

    public function store(Request $request): RedirectResponse
    {
        $agencyId = $this->agencyId();
        $validated = $this->validateEmployee($request, $agencyId);

        $validated['agency_id'] = $agencyId;
        $validated['created_by'] = auth()->id();
        $validated = array_merge($validated, $this->storeUploads($request, $agencyId));

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit($employee): View
    {
        $employee = $this->authorizeEmployee($employee);

        return view('employees.form', $this->formData($employee));
    }

    public function update(Request $request, $employee): RedirectResponse
    {
        $employee = $this->authorizeEmployee($employee);
        $agencyId = $this->agencyId();

        $validated = $this->validateEmployee($request, $agencyId, $employee->id);
        $validated['updated_by'] = auth()->id();
        $validated = array_merge($validated, $this->storeUploads($request, $agencyId, $employee));

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy($employee): RedirectResponse
    {
        $employee = $this->authorizeEmployee($employee);

        foreach (array_keys(self::DOC_FIELDS) as $field) {
            if ($employee->{$field}) {
                Storage::disk('public')->delete($employee->{$field});
            }
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted.');
    }

    /** Download a stored document — keeps files out of /storage-with-the-rest. */
    public function download($employee, string $field)
    {
        $employee = $this->authorizeEmployee($employee);
        abort_unless(array_key_exists($field, self::DOC_FIELDS), 404);
        abort_unless($employee->{$field}, 404);

        $name = $employee->{self::DOC_FIELDS[$field]} ?: basename($employee->{$field});

        return Storage::disk('public')->download($employee->{$field}, $name);
    }

    // ---------------------------------------------------------------- settings

    public function settings(): View
    {
        $agencyId = $this->agencyId();
        $groups = [];

        foreach (EmployeeSetting::TYPES as $type => $label) {
            $groups[$type] = EmployeeSetting::where('agency_id', $agencyId)
                ->where('type', $type)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return view('employees.settings', compact('groups'));
    }

    public function storeSetting(Request $request): RedirectResponse
    {
        $agencyId = $this->agencyId();

        $validated = $request->validate([
            'type'        => 'required|in:position,department,job_description',
            'name'        => 'required|string|max:191',
            'description' => 'nullable|string|max:2000',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $exists = EmployeeSetting::where('agency_id', $agencyId)
            ->where('type', $validated['type'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'That entry already exists in this list.'])->withInput();
        }

        EmployeeSetting::create([
            'agency_id'   => $agencyId,
            'type'        => $validated['type'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status'      => $validated['status'] ?? 'active',
        ]);

        return back()->with('success', 'Added to ' . EmployeeSetting::TYPES[$validated['type']] . '.');
    }

    public function updateSetting(Request $request, $setting): RedirectResponse
    {
        $setting = $this->authorizeSetting($setting);

        $validated = $request->validate([
            'name'        => 'required|string|max:191',
            'description' => 'nullable|string|max:2000',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $setting->update($validated);

        return back()->with('success', 'Updated.');
    }

    public function destroySetting($setting): RedirectResponse
    {
        $setting = $this->authorizeSetting($setting);

        $setting->delete();

        return back()->with('success', 'Removed.');
    }

    // ------------------------------------------------------------------ helpers

    private function agencyId(): int
    {
        $agencyId = (int) (auth()->user()->agency_id ?? 0);
        abort_if($agencyId === 0, 403, 'No agency context.');

        return $agencyId;
    }

    /**
     * Resolve the employee WITHOUT the tenant scope, then apply the agency
     * check this controller documents. Card 6b008b8d3ca32bfcb58ce970: Employee
     * is tenant-scoped now, and route-model binding would answer another
     * agency's id with a 404 before the 403 ever ran. Resolve-with-bypass
     * keeps the documented 403 (see the class docblock) and the check is
     * unconditional, so nothing unscoped ever reaches a response.
     */
    private function authorizeEmployee($employee): Employee
    {
        $employee = $employee instanceof Employee
            ? $employee
            : Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->findOrFail($employee);

        abort_unless((int) $employee->agency_id === $this->agencyId(), 403);

        return $employee;
    }

    /** Same pattern for the per-agency option rows. */
    private function authorizeSetting($setting): EmployeeSetting
    {
        $setting = $setting instanceof EmployeeSetting
            ? $setting
            : EmployeeSetting::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->findOrFail($setting);

        abort_unless((int) $setting->agency_id === $this->agencyId(), 403);

        return $setting;
    }

    private function formData(?Employee $employee): array
    {
        $agencyId = $this->agencyId();

        return [
            'employee'    => $employee,
            'branches'    => Branch::where('agency_id', $agencyId)->orderBy('name')->get(),
            'positions'   => EmployeeSetting::optionsFor($agencyId, 'position'),
            'departments' => EmployeeSetting::optionsFor($agencyId, 'department'),
            'statuses'    => Employee::EMPLOYMENT_STATUSES,
            'docStatuses' => Employee::DOCUMENT_STATUSES,
            'docFields'   => self::DOC_FIELDS,
        ];
    }

    private function validateEmployee(Request $request, int $agencyId, ?int $ignoreId = null): array
    {
        $uniqueNo = 'nullable|string|max:64';
        if ($request->filled('employee_no')) {
            $uniqueNo .= '|unique:employees,employee_no,' . ($ignoreId ?? 'NULL')
                . ',id,agency_id,' . $agencyId;
        }

        $branchRule = 'nullable|integer';
        return $request->validate([
            'employee_no'            => $uniqueNo,
            'full_name'              => 'required|string|max:255',
            'position'               => 'nullable|string|max:191',
            'department'             => 'nullable|string|max:191',
            'branch_id'              => $branchRule,
            'employment_status'      => 'nullable|in:' . implode(',', Employee::EMPLOYMENT_STATUSES),
            'date_hired'             => 'nullable|date',
            'educational_background' => 'nullable|string|max:5000',
            'job_description'        => 'nullable|string|max:5000',
            'nbi_reference'          => 'nullable|string|max:191',
            'nbi_issued_at'          => 'nullable|date',
            'nbi_expires_at'         => 'nullable|date',
            'training_records'       => 'nullable|string|max:5000',
            'competency_assessment'  => 'nullable|string|max:5000',
            'qms_orientation'        => 'nullable|string|max:5000',
            'document_status'        => 'nullable|in:' . implode(',', Employee::DOCUMENT_STATUSES),
            'appointment_document'   => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'nbi_document'           => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'training_document'      => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'qms_document'           => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);
    }

    /**
     * Move any uploaded document into the agency's folder and return the
     * path/name pairs to persist. The previous file is removed on replace.
     */
    private function storeUploads(Request $request, int $agencyId, ?Employee $existing = null): array
    {
        $out = [];

        foreach (self::DOC_FIELDS as $field => $nameField) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $path = $file->store("employees/{$agencyId}", 'public');

            if ($existing && $existing->{$field}) {
                Storage::disk('public')->delete($existing->{$field});
            }

            $out[$field] = $path;
            $out[$nameField] = $file->getClientOriginalName();
        }

        return $out;
    }
}
