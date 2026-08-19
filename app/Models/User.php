<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\BelongsToCompany;
use App\Traits\UUID;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UUID, BelongsToCompany;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'company_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Resolved result of isMainUser().
     */
    private ?bool $isMainUserCache = null;

    /**
     * Companies where this user is set as the main user.
     */
    public function managedCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'main_user', 'id');
    }

    /**
     * The user that manages every company, set through the SUPER_ADMIN_ID
     * env variable and read from config so it survives a cached config.
     */
    public function isSuperAdmin(): bool
    {
        return (string) $this->id === (string) config('company.super_admin_id');
    }

    /**
     * Whether the user is the main user of any company. Resolved once per
     * request since the navigation checks it on every page.
     */
    public function isMainUser(): bool
    {
        if ($this->isMainUserCache === null) {
            $this->isMainUserCache = $this->managedCompanies()->exists();
        }

        return $this->isMainUserCache;
    }

    /**
     * Only the super admin and the main user of a company may reach
     * the companies page.
     */
    public function canManageCompanies(): bool
    {
        return $this->isSuperAdmin() || $this->isMainUser();
    }

    /**
     * The super admin and the main users reach the records of every company,
     * every other user only reaches the records of their own company.
     */
    public function seesAllCompanies(): bool
    {
        return $this->canManageCompanies();
    }
}
