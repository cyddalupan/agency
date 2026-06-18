<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\Payment;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiAssistantController extends Controller
{
    /**
     * Prevented SQL operations.
     */
    private const FORBIDDEN_PATTERNS = [
        '/\bINSERT\b/i',
        '/\bUPDATE\b/i',
        '/\bDELETE\b/i',
        '/\bDROP\b/i',
        '/\bALTER\b/i',
        '/\bTRUNCATE\b/i',
        '/\bCREATE\b/i',
        '/\bREPLACE\b/i',
        '/\bGRANT\b/i',
        '/\bREVOKE\b/i',
        '/\bEXEC\b/i',
        '/\bEXECUTE\b/i',
        '/\bINTO\b/i',
        '/\bLOAD\s+FILE\b/i',
        '/\bINTO\s+OUTFILE\b/i',
    ];

    /**
     * Roles permitted to use the AI assistant.
     */
    private const ALLOWED_ROLES = [
        'admin', 'super_admin', 'manager', 'director',
        'staff', 'coordinator', 'recruiter', 'processor',
        'interviewer', 'report_viewer', 'billing', 'marketer',
    ];

    /**
     * Process a natural-language query and return structured data.
     */
    public function query(Request $request): JsonResponse
    {
        // Role check
        $user = $request->user();
        if (!in_array($user->user_type, self::ALLOWED_ROLES, true)) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        // Validate input
        $validated = $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $naturalQuery = trim($validated['query']);

        // Block dangerous SQL patterns
        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            if (preg_match($pattern, $naturalQuery)) {
                return response()->json(['error' => 'Only SELECT queries are allowed.'], 422);
            }
        }

        // Convert natural language to SQL
        $result = $this->parseToSql($naturalQuery, $user);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        // Execute the query within agency scope
        $agencyId = $user->agency_id;

        // Bind agency_id parameter for scoping
        $data = DB::select($result['sql'], array_merge(
            $result['bindings'] ?? [],
            ['agency_id' => $agencyId]
        ));

        // Log the AI query
        ActivityLog::create([
            'agency_id'   => $agencyId,
            'user_id'     => $user->id,
            'action'      => 'ai_query',
            'description' => "AI query: {$naturalQuery}",
            'metadata'    => [
                'natural_query' => $naturalQuery,
                'generated_sql' => $result['sql'],
            ],
        ]);

        return response()->json([
            'data'        => $data,
            'sql'         => $result['sql'],
            'explanation' => $result['explanation'],
        ]);
    }

    /**
     * Parse a natural-language query into a SELECT SQL statement.
     */
    private function parseToSql(string $query, User $user): array
    {
        $lower = strtolower($query);

        // Determine which table/entity the user is asking about
        if ($this->matchesEntity($lower, ['applicant', 'candidate', 'worker', 'personnel', 'employee'])) {
            return $this->buildApplicantQuery($query, $user);
        }

        if ($this->matchesEntity($lower, ['employer', 'company', 'client', 'customer'])) {
            return $this->buildEmployerQuery($query, $user);
        }

        if ($this->matchesEntity($lower, ['job', 'position', 'vacancy', 'opening'])) {
            return $this->buildJobQuery($query, $user);
        }

        if ($this->matchesEntity($lower, ['payment', 'billing', 'bill', 'commission'])) {
            return $this->buildBillingQuery($query, $user);
        }

        // Default: show applicants
        return $this->buildApplicantQuery($query, $user);
    }

    /**
     * Build a SELECT query for applicants.
     */
    private function buildApplicantQuery(string $query, User $user): array
    {
        $lower = strtolower($query);
        $conditions = [];
        $bindings = [];

        // Detect count requests
        if (preg_match('/\b(count|how many|total)\b/', $lower)) {
            $sql = 'SELECT COUNT(*) as total FROM applicants WHERE agency_id = :agency_id';
            return [
                'sql'         => $sql,
                'bindings'    => $bindings,
                'explanation' => 'Counting applicants matching your request.',
            ];
        }

        // Detect recent / latest
        if (preg_match('/\b(recent|latest|newest|last)\b/', $lower)) {
            $limit = 5;
            if (preg_match('/\b(\d+)\b/', $query, $m)) {
                $limit = (int) $m[1];
            }
            $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id ORDER BY created_at DESC LIMIT ' . $limit;
            return [
                'sql'         => $sql,
                'bindings'    => $bindings,
                'explanation' => "Showing the {$limit} most recently added applicants.",
            ];
        }

        // Detect name search
        if (preg_match('/(?:named|called|name is|name\s*:?\s*)\s*"([^"]+)"/i', $query, $m)) {
            $name = trim($m[1]);
            $parts = explode(' ', $name, 2);
            if (count($parts) === 2) {
                $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id AND first_name LIKE :first AND last_name LIKE :last ORDER BY created_at DESC';
                $bindings['first'] = '%' . $parts[0] . '%';
                $bindings['last'] = '%' . $parts[1] . '%';
            } else {
                $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id AND (first_name LIKE :name OR last_name LIKE :name) ORDER BY created_at DESC';
                $bindings['name'] = '%' . $name . '%';
            }
            return [
                'sql'         => $sql,
                'bindings'    => $bindings,
                'explanation' => "Searching for applicants named \"{$name}\".",
            ];
        }

        // Detect Find X named Y (e.g. "Find applicant Juan Dela Cruz")
        if (preg_match('/\bfind\s+(?:applicant|applicants|candidate|candidates)?\s*([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/', $query, $m)) {
            $name = trim($m[1]);
            $parts = explode(' ', $name, 2);
            if (count($parts) === 2) {
                $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id AND first_name LIKE :first AND last_name LIKE :last ORDER BY created_at DESC';
                $bindings['first'] = '%' . $parts[0] . '%';
                $bindings['last'] = '%' . $parts[1] . '%';
            } else {
                $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id AND (first_name LIKE :first OR last_name LIKE :last) ORDER BY created_at DESC';
                $bindings['first'] = '%' . $parts[0] . '%';
                $bindings['last'] = '%' . $parts[0] . '%';
            }
            return [
                'sql'         => $sql,
                'bindings'    => $bindings,
                'explanation' => "Searching for applicant named \"{$name}\".",
            ];
        }

        // Detect free-text name search (e.g. "applicants named Alice")
        if (preg_match('/\b(named|called)\s+([A-Za-z]+)/', $query, $m)) {
            $searchName = $m[2];
            $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id AND (first_name LIKE :name OR last_name LIKE :name) ORDER BY created_at DESC';
            $bindings['name'] = '%' . $searchName . '%';
            return [
                'sql'         => $sql,
                'bindings'    => $bindings,
                'explanation' => "Searching for applicants named \"{$searchName}\".",
            ];
        }

        // Detect "all" or "list" with no specific filter
        if (preg_match('/\b(all|show|list|display|get|give)\b/', $lower)) {
            $limit = 50;
            if (preg_match('/\b(\d+)\b/', $query, $m)) {
                $limit = min((int) $m[1], 100);
            }
            $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id ORDER BY created_at DESC LIMIT ' . $limit;
            return [
                'sql'         => $sql,
                'bindings'    => $bindings,
                'explanation' => "Listing up to {$limit} applicants from your agency.",
            ];
        }

        // Default: list recent applicants
        $sql = 'SELECT id, first_name, middle_name, last_name, email, contact, status_code, created_at FROM applicants WHERE agency_id = :agency_id ORDER BY created_at DESC LIMIT 20';
        return [
            'sql'         => $sql,
            'bindings'    => $bindings,
            'explanation' => 'Showing recent applicants from your agency.',
        ];
    }

    /**
     * Build a SELECT query for employers.
     */
    private function buildEmployerQuery(string $query, User $user): array
    {
        $lower = strtolower($query);

        if (preg_match('/\b(count|how many|total)\b/', $lower)) {
            return [
                'sql'         => 'SELECT COUNT(*) as total FROM employers WHERE agency_id = :agency_id',
                'bindings'    => [],
                'explanation' => 'Counting employers in your agency.',
            ];
        }

        $limit = 50;
        if (preg_match('/\b(\d+)\b/', $query, $m)) {
            $limit = min((int) $m[1], 100);
        }

        return [
            'sql'         => 'SELECT id, company_no, name, contact_person, contact, email, country_id, created_at FROM employers WHERE agency_id = :agency_id ORDER BY created_at DESC LIMIT ' . $limit,
            'bindings'    => [],
            'explanation' => "Listing up to {$limit} employers from your agency.",
        ];
    }

    /**
     * Build a SELECT query for jobs/positions.
     */
    private function buildJobQuery(string $query, User $user): array
    {
        $lower = strtolower($query);

        if (preg_match('/\b(count|how many|total)\b/', $lower)) {
            return [
                'sql'         => 'SELECT COUNT(*) as total FROM job_positions WHERE agency_id = :agency_id',
                'bindings'    => [],
                'explanation' => 'Counting job positions in your agency.',
            ];
        }

        $limit = 50;
        if (preg_match('/\b(\d+)\b/', $query, $m)) {
            $limit = min((int) $m[1], 100);
        }

        return [
            'sql'         => 'SELECT id, position_id, salary, total_slots, occupied, employer_id, status, created_at FROM job_positions WHERE agency_id = :agency_id ORDER BY created_at DESC LIMIT ' . $limit,
            'bindings'    => [],
            'explanation' => "Listing up to {$limit} job positions from your agency.",
        ];
    }

    /**
     * Build a SELECT query for billing-related data.
     */
    private function buildBillingQuery(string $query, User $user): array
    {
        $lower = strtolower($query);

        if (preg_match('/\b(commission|commissions)\b/', $lower)) {
            return [
                'sql'         => 'SELECT id, employer_id, type, amount, status, created_at FROM commissions WHERE agency_id = :agency_id ORDER BY created_at DESC LIMIT 20',
                'bindings'    => [],
                'explanation' => 'Showing recent commissions from your agency.',
            ];
        }

        return [
            'sql'         => 'SELECT id, employer_id, applicant_id, employer_cost, applicant_cost, employer_deposit, applicant_deposit, status, created_at FROM bills WHERE agency_id = :agency_id ORDER BY created_at DESC LIMIT 20',
            'bindings'    => [],
            'explanation' => 'Showing recent bills from your agency.',
        ];
    }

    /**
     * Check if the query mentions any of the given entity keywords.
     */
    private function matchesEntity(string $lowerQuery, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($lowerQuery, $keyword)) {
                return true;
            }
        }
        return false;
    }

    // ═══════════════════════════════════════════════════════════════════
    // PRE-BUILT ANALYTICS TEMPLATES
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Return the list of pre-built analytics query templates.
     */
    public function templates(): JsonResponse
    {
        $templates = $this->getTemplateDefinitions();

        return response()->json([
            'templates' => collect($templates)->map(function (array $def, string $id): array {
                return [
                    'id'          => $id,
                    'name'        => $def['name'],
                    'description' => $def['description'],
                    'category'    => $def['category'],
                ];
            })->values()->all(),
        ]);
    }

    /**
     * Execute a named template query.
     */
    public function executeTemplate(string $template): JsonResponse
    {
        $templates = $this->getTemplateDefinitions();

        if (!isset($templates[$template])) {
            return response()->json(['error' => 'Template not found.'], 404);
        }

        $def = $templates[$template];
        $user = auth()->user();
        $agencyId = $user->agency_id;

        $bindings = array_merge($def['bindings'] ?? [], ['agency_id' => $agencyId]);
        $data = DB::select($def['sql'], $bindings);

        return response()->json([
            'data'        => $data,
            'sql'         => $def['sql'],
            'explanation' => $def['explanation'],
            'template_id' => $template,
        ]);
    }

    /**
     * Export query results (natural language or template) as a CSV download.
     */
    public function export(Request $request): JsonResponse|\Illuminate\Http\Response
    {
        $user = auth()->user();
        $agencyId = $user->agency_id;
        $query = trim($request->query('query', ''));

        if (empty($query)) {
            return response()->json(['error' => 'Query parameter is required.'], 422);
        }

        // Check if it's a template query
        if (str_starts_with($query, 'template:')) {
            $templateId = substr($query, 9);
            $templates = $this->getTemplateDefinitions();

            if (!isset($templates[$templateId])) {
                return response()->json(['error' => 'Template not found.'], 404);
            }

            $def = $templates[$templateId];
            $bindings = array_merge($def['bindings'] ?? [], ['agency_id' => $agencyId]);
            $data = DB::select($def['sql'], $bindings);
        } else {
            // Parse natural language query, same as the POST endpoint
            $naturalQuery = trim($query);

            // Block dangerous patterns
            foreach (self::FORBIDDEN_PATTERNS as $pattern) {
                if (preg_match($pattern, $naturalQuery)) {
                    return response()->json(['error' => 'Only SELECT queries are allowed.'], 422);
                }
            }

            $result = $this->parseToSql($naturalQuery, $user);

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 422);
            }

            $data = DB::select($result['sql'], array_merge(
                $result['bindings'] ?? [],
                ['agency_id' => $agencyId]
            ));

            // Keep the raw SQL for header extraction later
            $raw_sql = $result['sql'];
        }

        $data = collect($data);

        // Determine headers — from data if present, otherwise parse from SQL SELECT clause
        if ($data->isNotEmpty()) {
            $headers = array_keys((array) $data->first());
        } elseif (isset($raw_sql)) {
            $headers = $this->extractSelectColumns($raw_sql);
        } else {
            $headers = [];
        }

        // Build CSV as string
        $csv = '';
        $handle = fopen('php://temp', 'w+b');

        if (!empty($headers)) {
            fputcsv($handle, $headers);
        }

        foreach ($data as $row) {
            fputcsv($handle, (array) $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ai-query-results.csv"',
        ]);
    }

    /**
     * Extract column names from a SELECT SQL statement.
     */
    private function extractSelectColumns(string $sql): array
    {
        // Match everything between SELECT and FROM (or INTO)
        if (preg_match('/\bSELECT\s+(.*?)\s+FROM\s+/is', $sql, $m)) {
            $cols = trim($m[1]);

            // Split by commas, handling AS clauses
            $parts = explode(',', $cols);
            $columns = [];

            foreach ($parts as $part) {
                $part = trim($part);

                // If has alias (AS or just trailing identifier), use alias
                if (preg_match('/\s+AS\s+([`"]?)(\w+)\1$/i', $part, $am)) {
                    $columns[] = $am[2];
                } elseif (preg_match('/\s+([a-zA-Z_]\w*)$/', $part, $fm)) {
                    $columns[] = $fm[1];
                } elseif (preg_match('/\b(\w+)$/', $part, $cm)) {
                    $columns[] = $cm[1];
                } else {
                    $columns[] = $part;
                }
            }

            return $columns;
        }

        return [];
    }

    /**
     * Return the full template definitions array.
     */
    private function getTemplateDefinitions(): array
    {
        return [
            'top_applicants_by_status' => [
                'name'        => 'Top Applicants by Status',
                'description' => 'Group and count applicants by their current status code.',
                'category'    => 'applicants',
                'sql'         => 'SELECT status_code, COUNT(*) as total FROM applicants WHERE agency_id = :agency_id GROUP BY status_code ORDER BY total DESC',
                'explanation' => 'Shows how many applicants are in each status stage.',
            ],
            'monthly_deployment_stats' => [
                'name'        => 'Monthly Deployment Stats',
                'description' => 'Count of deployed applicants grouped by month.',
                'category'    => 'deployments',
                'sql'         => "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total FROM applicants WHERE agency_id = :agency_id AND status_code = 8 GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC",
                'explanation' => 'Number of deployed applicants per month (status_code = 8).',
            ],
            'billing_summary' => [
                'name'        => 'Billing Summary',
                'description' => 'Summary of billing data including total employer cost, deposits, and payments.',
                'category'    => 'billing',
                'sql'         => 'SELECT COUNT(*) as total_bills, COALESCE(SUM(employer_cost), 0) as total_employer_cost, COALESCE(SUM(applicant_cost), 0) as total_applicant_cost FROM bills WHERE agency_id = :agency_id',
                'explanation' => 'Total bills, employer cost, and applicant cost from your billing records.',
            ],
            'employer_rankings' => [
                'name'        => 'Employer Rankings',
                'description' => 'Employers ranked by number of applicants placed.',
                'category'    => 'employers',
                'sql'         => 'SELECT e.id, e.name, e.company_no, COUNT(a.id) as applicant_count FROM employers e LEFT JOIN applicants a ON a.employer_id = e.id AND a.agency_id = e.agency_id WHERE e.agency_id = :agency_id GROUP BY e.id, e.name, e.company_no ORDER BY applicant_count DESC',
                'explanation' => 'Employers listed by the number of applicants they have, highest first.',
            ],
            'status_pipeline_breakdown' => [
                'name'        => 'Status Pipeline Breakdown',
                'description' => 'Full pipeline breakdown showing count of applicants at each stage.',
                'category'    => 'applicants',
                'sql'         => 'SELECT status_code, COUNT(*) as total FROM applicants WHERE agency_id = :agency_id GROUP BY status_code ORDER BY status_code ASC',
                'explanation' => 'Complete breakdown of applicants across every stage of the pipeline.',
            ],
        ];
    }
}
