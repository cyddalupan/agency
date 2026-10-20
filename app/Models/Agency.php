<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'subdomain', 'logo', 'settings', 'status', 'address', 'city', 'email', 'contact_person', 'num_branches', 'company_profile', 'page_tabs'];

    protected $casts = [
        'settings' => 'json',
        'page_tabs' => 'array',
    ];

    public function getCompanyProfileHtmlAttribute(): string
    {
        return $this->company_profile ?: '';
    }

    /**
     * Content for one of the Company Profile page tabs.
     *
     * The first tab falls back to the legacy `company_profile` column so content
     * written before the page had tabs keeps showing up.
     */
    public function pageTabContent(string $tab): string
    {
        $tabs = is_array($this->page_tabs) ? $this->page_tabs : [];

        if (isset($tabs[$tab]) && trim((string) $tabs[$tab]) !== '') {
            return (string) $tabs[$tab];
        }

        if ($tab === 'company-profile') {
            return (string) ($this->company_profile ?: '');
        }

        return '';
    }

    /**
     * Persist content for a single tab, leaving the other tabs untouched.
     */
    public function setPageTabContent(string $tab, string $html): void
    {
        $tabs = is_array($this->page_tabs) ? $this->page_tabs : [];
        $tabs[$tab] = $html;

        $attributes = ['page_tabs' => $tabs];

        // Keep the legacy column in sync for the first tab.
        if ($tab === 'company-profile') {
            $attributes['company_profile'] = $html;
        }

        $this->update($attributes);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
