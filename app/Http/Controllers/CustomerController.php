<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //
    function index()
    {
        $customers = Customer::all();
        return view('customers.index')->with('customers', $customers);
    }

    function show(Customer $customer)
    {
        return view('customers.show')->with('customer', $customer);
    }
}
