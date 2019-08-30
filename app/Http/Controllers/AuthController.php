<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Validator;
use App\Customer;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    private $expirationTime = 60*60;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }
    /**
     * Create a new token.
     *
     * @param  \App\Customer   $customer
     * @return string
     */
    protected function jwt(Customer $customer) {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $customer->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + $this->expirationTime // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    }
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param  \App\Customer   $customer
     * @return mixed
     */
    public function authenticate() {
        $this->validate($this->request, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);
        $customer = Customer::where('email', $this->request->input('email'))->first();
        if (!$customer) {
            return response()->json([
                'error' => 'Email or password is wrong.'
            ], 400);
        }

        if (Hash::check($this->request->input('password'), $customer->password)) {
            return $this->respondWithToken($customer);
        }

        return response()->json([
            'error' => 'Email or password is wrong.'
        ], 400);
    }

    public function register()
    {
        $this->validate($this->request, [
            'firstname' => 'required',
            'lastname'  => 'required',
            'email'     => 'required|email|unique:customers',
            'password'  => 'required'
        ]);

        $customer = Customer::create([
            'firstname' => $this->request->input('firstname'),
            'lastname'  => $this->request->input('lastname'),
            'email'     => $this->request->input('email'),
            'password'  =>  Hash::make($this->request->input('password')),
        ]);

        return $this->respondWithToken($customer);
    }

    protected function respondWithToken($customer)
    {
        return response()->json([
            'token'         => $this->jwt($customer),
            'token_type'    => 'bearer',
            'expires_in'   => time() + $this->expirationTime,
        ], 200);
    }



}