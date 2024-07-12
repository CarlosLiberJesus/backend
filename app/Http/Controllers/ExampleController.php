<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        return json_encode(app()->version());
    }

    public function secure()
    {
        return "what is this middleware for?";
    }
}
