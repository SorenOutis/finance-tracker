<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
            'account_name' => ['required', 'string', 'max:255'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            Account::create([
                'user_id' => $user->id,
                'name' => $input['account_name'],
                'type' => 'cash',
                'initial_balance' => $input['initial_balance'],
                'currency' => $input['currency'] ?? 'PHP',
            ]);

            return $user;
        });
    }
}
