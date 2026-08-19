<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = $this->companiesOf($request->user())
            ->with('mainUser')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('companies.index', [
            'companies' => $companies,
            'users' => User::select()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();

        $company = new Company();
        $company->name = $data['name'];
        $company->status = $data['status'] == 1 ? true : false;
        $company->main_user = $data['main_user'] ?? null;
        $company->slug = $data['slug'];
        $company->site_title = $data['site_title'] ?? null;
        $company->logo_url = $data['logo_url'] ?? null;
        $company->save();

        return redirect('companies/' . $company->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company, Request $request)
    {
        $this->authorizeCompany($company, $request->user());

        return view('companies.show', [
            'company' => $company,
            'users' => User::select()->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Company $company, UpdateCompanyRequest $request)
    {
        $this->authorizeCompany($company, $request->user());

        $data = $request->validated();

        $company->name = $data['name'];
        $company->status = $data['status'] == 1 ? true : false;
        $company->main_user = $data['main_user'] ?? null;
        $company->slug = $data['slug'];
        $company->site_title = $data['site_title'] ?? null;
        $company->logo_url = $data['logo_url'] ?? null;
        $company->save();

        return redirect('companies/' . $company->id)->with('editMessage', 'A empresa foi atualizada com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company, Request $request)
    {
        $this->authorizeCompany($company, $request->user());

        $message = 'A empresa possui operadores, clientes ou pagamentos vinculados e não pode ser removida';

        if (!$company->hasRelatedRecords()) {
            $company->delete();
            $message = 'A empresa foi removida com sucesso';
        }

        return redirect('companies')->with('message', $message);
    }

    /**
     * The companies the given user is allowed to see: everything for the
     * super admin, only their own companies for a main user.
     */
    private function companiesOf(User $user)
    {
        $companies = Company::select();

        if (!$user->isSuperAdmin()) {
            $companies = $companies->where('main_user', $user->id);
        }

        return $companies;
    }

    /**
     * Block access to a company the user does not manage.
     */
    private function authorizeCompany(Company $company, User $user): void
    {
        if (!$user->isSuperAdmin() && $company->main_user != $user->id) {
            abort(403);
        }
    }
}
