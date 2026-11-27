<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === User::ROLE_DEVELOPER, 404);

        return view('plans.index', ['user' => $request->user()]);
    }
}
