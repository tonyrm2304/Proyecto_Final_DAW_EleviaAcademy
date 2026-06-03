<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Contacto');
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'accept_policy' => ['accepted'],
        ]);

        return back()->with('success', 'Gracias por contactar con Elevia Academy. Te responderemos en menos de 24h laborables.');
    }
}
