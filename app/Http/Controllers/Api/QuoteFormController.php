<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\QuoteFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class QuoteFormController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'service' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Mail::to(env('MAIL_FROM_ADDRESS', 'hello@example.com'))->send(new QuoteFormMail($request->all()));

        return response()->json(['message' => 'Your quote request has been sent successfully!']);
    }
}
