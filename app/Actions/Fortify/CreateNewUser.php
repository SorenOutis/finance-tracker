<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => Str::lower($input['email']),
            'password' => $input['password'],
            'terms_accepted_at' => now(),
            'terms_version' => config('auth.terms_version'),
            'must_reset_password' => false,
            'is_onboarded' => false,
        ]);
    }
}
