<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    /**
     * Display tenant inactive error page.
     *
     * @return \Illuminate\View\View
     */
    public function tenantInactive()
    {
        return view('errors.tenant-inactive');
    }

    /**
     * Display tenant required error page.
     *
     * @return \Illuminate\View\View
     */
    public function tenantRequired()
    {
        return view('errors.tenant-required');
    }
}
