<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Mail\UserWelcomeMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:20'],
            'department' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'first_name' => $input['first_name'],
            'middle_name' => $input['middle_name'] ?? null,
            'surname' => $input['surname'],
            'email' => $input['email'],
            'phone_number' => $input['phone_number'],
            'department' => $input['department'],
            'role' => 'user', // Default role
            'password' => Hash::make($input['password']),
        ]);

        Mail::mailer('noreply')->to($user->email)->send(new UserWelcomeMail($user));

        return $user;
    }
}