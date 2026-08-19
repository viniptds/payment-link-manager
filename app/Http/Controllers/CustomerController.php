<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    function index(Request $request)
    {
        $customers = Customer::visibleTo($request->user())->orderByDesc('created_at')->paginate(15);
        return view('customers.index')->with('customers', $customers);
    }

    function show(Customer $customer, Request $request)
    {
        if (!$customer->isVisibleTo($request->user())) {
            abort(403);
        }

        return view('customers.show')->with('customer', $customer);
    }
}
