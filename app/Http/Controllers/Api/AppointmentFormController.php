<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentFormController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'message' => 'required|string',
        ]);

        Mail::to(env('MAIL_TO_ADDRESS', 'info@ecr-ts.com'))->send(new AppointmentFormMail($request->all()));

        return response()->json(['message' => 'Your appointment has been booked successfully!']);
    }
}
