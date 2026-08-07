<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Country;
use App\Models\Employer;
use App\Models\ReportTemplate;
use App\Models\StatusCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportBuilder
{
    private ?ReportTemplate $template = null;
    private ?string $type = null;
    private array $config = [];

    public function __construct(?ReportTemplate $template = null)
    {
        if ($template) {
            $this->fromTemplate($template);
        }
    }

    /**
     * Create a builder for a given report type.
     */
    public static function forType(string $type): self
    {
        $supported = ['applicant_report', 'statistics', 'transactions'];
        if (!in_array($type, $supported)) {
            throw new \InvalidArgumentException("Unsupported report type: {$type}");
        }

        return new self();
    }

    /**
     * Load configuration from a saved template.
     */
    public function fromTemplate(ReportTemplate $template): self
    {
        $this->template = $template;
        $this->type = $template->type;
        $this->config = $template->config ?? [];

        return $this;
    }

    /**
     * Execute the report query and return the result as a collection.
     */
    public function get(): Collection
    {
        return $this->buildReport();
    }

    /**
     * Get paginated results.
     */
    public function paginate(int $perPage = 25)
    {
        $rows = $this->buildReport();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage(request()->get('page', 1), $perPage)->values(),
            $rows->count(),
            $perPage,
            request()->get('page', 1),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Build the report data based on type and config.
     */
    private function buildReport(): Collection
    {
        if ($this->type === 'applicant_report') {
            return $this->buildApplicantReport();
        }

        throw new \InvalidArgumentException("Unsupported report type: {$this->type}");
    }

    /**
     * Build applicant report with mapped columns.
     */
    private function buildApplicantReport(): Collection
    {
        $agencyId = Auth::user()->agency_id;
        $config = $this->config;

        $columns = $config['columns'] ?? ['name', 'status', 'country', 'created_at'];

        $query = Applicant::where('agency_id', $agencyId)
            ->forBranchUser()
            ->with(['statusCode', 'country', 'employer']);

        // Apply date preset filter
        $this->applyDatePreset($query, $config['date_preset'] ?? null);

        // Apply sorting
        $sortBy = $config['sort_by'] ?? 'created_at';
        $sortOrder = $config['sort_order'] ?? 'desc';
        $this->applySorting($query, $sortBy, $sortOrder);

        $applicants = $query->get();
        $groupBy = $config['group_by'] ?? null;

        // Map applicants to column values
        $rows = $applicants->map(fn($app) => $this->mapRow($app, $columns));

        // Apply grouping if set
        if ($groupBy && isset($columns[$groupBy])) {
            $rows = $rows->groupBy($groupBy);
        }

        return $rows;
    }

    /**
     * Map an applicant to a flat row with resolved column values.
     */
    private function mapRow(Applicant $applicant, array $columns): array
    {
        $row = [];

        foreach ($columns as $col) {
            $row[$col] = match ($col) {
                'name' => trim($applicant->first_name . ' ' . $applicant->last_name),
                'email' => $applicant->email ?? '',
                'phone' => $applicant->contact_no ?? $applicant->phone ?? '',
                'gender' => $applicant->gender ?? '',
                'country' => $applicant->country?->name ?? '',
                'status' => $applicant->statusCode?->label ?? 'Pending',
                'position' => $applicant->position?->name ?? '',
                'employer' => $applicant->employer?->name ?? '',
                'salary' => $applicant->expected_salary ? number_format($applicant->expected_salary, 2) : '',
                'source' => $applicant->source ?? '',
                'agent' => $applicant->agent?->name ?? '',
                'created_at' => $applicant->created_at?->format('Y-m-d H:i') ?? '',
                'updated_at' => $applicant->updated_at?->format('Y-m-d H:i') ?? '',
                default => $applicant->{$col} ?? '',
            };
        }

        return $row;
    }

    /**
     * Apply a date preset filter to the query.
     */
    private function applyDatePreset($query, ?string $preset): void
    {
        if (!$preset) {
            return;
        }

        $now = now();
        $from = match ($preset) {
            'today' => $now->startOfDay(),
            'this_week' => $now->startOfWeek(),
            'this_month' => $now->startOfMonth(),
            'last_month' => $now->subMonth()->startOfMonth(),
            'this_quarter' => $now->startOfQuarter(),
            'this_year' => $now->startOfYear(),
            default => null,
        };

        if ($from) {
            $to = match ($preset) {
                'today' => $from->copy()->endOfDay(),
                'this_week' => $from->copy()->endOfWeek(),
                'this_month' => $from->copy()->endOfMonth(),
                'last_month' => $from->copy()->endOfMonth(),
                'this_quarter' => $from->copy()->endOfQuarter(),
                'this_year' => $from->copy()->endOfYear(),
                default => $now,
            };
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, string $sortBy, string $sortOrder): void
    {
        switch ($sortBy) {
            case 'name':
                $query->orderBy('first_name', $sortOrder)->orderBy('last_name', $sortOrder);
                break;
            case 'email':
                $query->orderBy('email', $sortOrder);
                break;
            case 'phone':
                $query->orderBy('contact_no', $sortOrder);
                break;
            case 'gender':
                $query->orderBy('gender', $sortOrder);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
    }
}
