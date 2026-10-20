<?php

namespace App\Support;

/**
 * The Company Profile page is split into four tabs. Each tab is a separate
 * sanitized HTML block stored per agency (agencies.page_tabs JSON).
 */
class CompanyProfileTabs
{
    public const COMPANY_PROFILE = 'company-profile';
    public const ORGANIZATIONAL_CHART = 'organizational-chart';
    public const RECRUITMENT_PROCESS = 'recruitment-process';
    public const SYSTEM_USER_GUIDE = 'system-user-guide';

    /**
     * slug => [label, icon, description, empty state hint]
     */
    public const TABS = [
        self::COMPANY_PROFILE => [
            'label' => 'Company Profile',
            'icon' => '🏢',
            'description' => 'Who the agency is — history, mission, services and offices.',
        ],
        self::ORGANIZATIONAL_CHART => [
            'label' => 'Organizational Chart',
            'icon' => '🗂️',
            'description' => 'How the agency is structured — upload the chart image or describe the reporting lines.',
        ],
        self::RECRUITMENT_PROCESS => [
            'label' => 'Recruitment Process',
            'icon' => '🧭',
            'description' => 'The steps an applicant goes through, from sourcing to deployment.',
        ],
        self::SYSTEM_USER_GUIDE => [
            'label' => 'System User Guide',
            'icon' => '📖',
            'description' => 'How to use Landas, module by module.',
        ],
    ];

    public static function slugs(): array
    {
        return array_keys(self::TABS);
    }

    public static function isValid(string $tab): bool
    {
        return array_key_exists($tab, self::TABS);
    }

    public static function label(string $tab): string
    {
        return self::TABS[$tab]['label'] ?? 'Company Profile';
    }

    /**
     * Starter content used when the System User Guide tab is still empty, so the
     * agency starts from a usable outline instead of a blank page.
     */
    public static function userGuideStarter(): string
    {
        return <<<'HTML'
<h1>Landas System User Guide</h1>
<p><em>Edit this guide to match how your agency actually works. Everything here is editable and visible to your team.</em></p>
<h2>1. Getting started</h2>
<ul>
<li>Sign in with the email or username provided by your administrator.</li>
<li>The sidebar only shows the modules your access level allows.</li>
<li>Use the branch selector in the top bar when you work across multiple branches.</li>
</ul>
<h2>2. Applicants</h2>
<ul>
<li><strong>Applicants</strong> lists everyone registered under your agency, filtered by category (skilled, household, etc.).</li>
<li>Open an applicant to work through the tabs: Personal Information, Requirements, Cases, Medical, and Deployment.</li>
<li><strong>Add Applicant</strong> creates a new record; duplicate names and birthdays are flagged before saving.</li>
</ul>
<h2>3. FRA (Foreign Recruitment Agency)</h2>
<ul>
<li>Maintain the foreign principals you deploy to, including their requirements and job orders.</li>
<li>Cases link an applicant to an FRA and track the deployment stage.</li>
</ul>
<h2>4. Reference data</h2>
<ul>
<li><strong>Branches</strong>, <strong>Countries</strong>, <strong>Positions</strong>, <strong>Status Codes</strong>, <strong>Languages</strong> and <strong>Skills</strong> power the dropdowns used across the system — keep them tidy.</li>
</ul>
<h2>5. Accounting</h2>
<ul>
<li><strong>Receivables</strong> and <strong>Expense and Payments</strong> track what is billed and what has been collected.</li>
<li><strong>Accounts</strong> holds the chart of accounts used by those entries.</li>
<li><strong>Statistics</strong> summarises placements and collections per period.</li>
</ul>
<h2>6. Reports</h2>
<ul>
<li>Reports export applicant and financial data for a chosen date range.</li>
<li>Reports are scoped to your branch unless your access level allows agency-wide data.</li>
</ul>
<h2>7. Users and access levels</h2>
<ul>
<li>Administrators create users under <strong>Users</strong> and assign an access level: Admin, Accounting, Recruitment, Welfare, Receptionist, Processing, Paralegal, Branch, Operation or Encoder.</li>
<li>Access levels control both the sidebar and what each user can change.</li>
</ul>
<h2>8. Settings</h2>
<ul>
<li><strong>Agency Settings</strong> controls branding, logo and which sidebar links are visible.</li>
<li>Company Profile, Organizational Chart, Recruitment Process and this User Guide are maintained from this page.</li>
</ul>
HTML;
    }
}
