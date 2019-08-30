<?php
/**
 * Created by PhpStorm.
 * User: Стас
 * Date: 30.08.2019
 * Time: 14:30
 */

namespace App\Http\Controllers;


use App\Customer;

class CustomersController extends Controller
{
    public function getCustomers() {
        return response()->json(Customer::all());
    }
}