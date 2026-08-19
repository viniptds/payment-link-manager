<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    /**
     * Company that answers for every request that cannot be resolved
     * through the subdomain.
     */
    const FALLBACK_ID = 1;

    /**
     * Subdomains that never belong to a company.
     */
    const RESERVED_SLUGS = ['www', 'app', 'admin', 'api', 'mail'];

    /**
     * Container key holding the company resolved for the current request.
     */
    const CONTAINER_KEY = 'company.current';

    protected $fillable = ['name', 'status', 'main_user', 'slug', 'site_title', 'logo_url'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function mainUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_user', 'id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Whether the company still has records attached to it.
     */
    public function hasRelatedRecords(): bool
    {
        return $this->users()->exists()
            || $this->customers()->exists()
            || $this->payments()->exists();
    }

    /**
     * The company resolved for the current request by ResolveCompany.
     */
    public static function current(): ?self
    {
        return app()->bound(self::CONTAINER_KEY) ? app(self::CONTAINER_KEY) : null;
    }

    /**
     * The company every unresolved request belongs to.
     */
    public static function fallback(): ?self
    {
        return static::find(self::FALLBACK_ID);
    }

    /**
     * Active company owning the given host, falling back to the company
     * that answers for the whole application.
     */
    public static function fromHost(?string $host): ?self
    {
        $slug = static::slugFromHost($host);

        $company = $slug
            ? static::where('slug', $slug)->where('status', true)->first()
            : null;

        return $company ?? static::fallback();
    }

    /**
     * Left-most label of the host when it sits in front of the application
     * domain, e.g. "acme" for acme.mydomain.com.
     */
    public static function slugFromHost(?string $host): ?string
    {
        if (empty($host) || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $host = strtolower($host);
        $baseHost = static::baseHost();

        if ($baseHost && $host === $baseHost) {
            return null;
        }

        if ($baseHost && str_ends_with($host, '.' . $baseHost)) {
            $subdomain = substr($host, 0, -strlen('.' . $baseHost));
        } else {
            // The application domain is not configured for this host, so a
            // subdomain is only assumed when there is a domain behind it.
            $labels = explode('.', $host);
            $subdomain = count($labels) > 2 ? $labels[0] : null;
        }

        if (empty($subdomain)) {
            return null;
        }

        $slug = explode('.', $subdomain)[0];

        return in_array($slug, self::RESERVED_SLUGS) ? null : $slug;
    }

    /**
     * Address serving this company, e.g. https://acme.mydomain.com.
     */
    public function url(): ?string
    {
        $baseHost = static::baseHost();

        if (empty($this->slug) || empty($baseHost)) {
            return null;
        }

        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme . '://' . $this->slug . '.' . $baseHost;
    }

    /**
     * Title of the company site, falling back to the application settings.
     */
    public function title(): string
    {
        return $this->site_title ?: config('settings.app_name', 'App');
    }

    /**
     * Logo of the company site, falling back to the application settings.
     * Accepts both absolute urls and paths inside the public folder.
     */
    public function logo(): string
    {
        if (empty($this->logo_url)) {
            return asset('storage/assets/' . config('settings.logo_main', ''));
        }

        return Str::startsWith($this->logo_url, ['http://', 'https://', '//', '/'])
            ? $this->logo_url
            : asset($this->logo_url);
    }

    /**
     * Application settings this company overrides, merged over the global
     * ones by the ResolveCompany middleware. Every new company column that
     * belongs to the site can be added here.
     */
    public function settings(): array
    {
        return [
            'app_name' => $this->title(),
            'logo_url' => $this->logo(),
            'company_slug' => $this->slug,
        ];
    }

    /**
     * Host of the application, without the www prefix.
     */
    private static function baseHost(): ?string
    {
        $baseHost = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?? '');

        return preg_replace('/^www\./', '', $baseHost) ?: null;
    }
}
