<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveCompany
{
    /**
     * Route the request to the settings of the company owning the host, e.g.
     * acme.mydomain.com loads the company with the "acme" slug from the
     * database and its settings take over the application ones. Hosts that
     * do not reach a company are served by the fallback company.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = null;

        try {
            $company = Company::fromHost($request->getHost());
        } catch (\Throwable $e) {
            // The companies table is not available yet
        }

        app()->instance(Company::CONTAINER_KEY, $company);

        if ($company) {
            config()->set('settings', array_merge(
                config('settings', []),
                array_filter($company->settings(), fn ($value) => !is_null($value))
            ));
        }

        View::share('currentCompany', $company);

        return $next($request);
    }
}
