<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Keep only the records of the company of the given user. The super admin
     * and the main users of a company are not restricted.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (!$user || $user->seesAllCompanies()) {
            return $query;
        }

        return $query->where($this->getTable() . '.company_id', $user->company_id);
    }

    /**
     * Whether the given user is allowed to reach this record.
     */
    public function isVisibleTo(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->seesAllCompanies() || $this->company_id == $user->company_id;
    }
}
