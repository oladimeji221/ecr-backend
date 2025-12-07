<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        Mail::to(env('MAIL_TO_ADDRESS', 'info@ecr-ts.com'))->send(new ContactFormMail($request->all()));

        return response()->json(['message' => 'Your message has been sent successfully!']);
    }
}
