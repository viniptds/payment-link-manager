<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends Controller
{
    function index()
    {
        $customers = Customer::select()->orderByDesc('created_at')->paginate(15);
        return view('customers.index')->with('customers', $customers);
    }

    function show(Customer $customer)
    {
        return view('customers.show')->with('customer', $customer);
    }
}
