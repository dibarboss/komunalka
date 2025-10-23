<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // Якщо користувач вже авторизований, перенаправляємо в панель
        if (auth()->check()) {
            return redirect()->route('filament.dashboard.pages.dashboard');
        }

        return view('landing');
    }
}
