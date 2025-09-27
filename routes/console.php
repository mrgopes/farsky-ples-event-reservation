<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 *     Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.store');

 */

Artisan::command('make:admin {name}', function (string $name) {
    $this->comment("Creating admin user: $name");

    $email = $this->ask('Enter email for the admin user');
    $password = $this->secret('Enter password for the admin user');

    $data = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
    ];

    $validator = Validator::make($data, [
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255|unique:App\Models\User,email',
        'password' => [ 'required', Rules\Password::defaults() ],
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }
        return 1;
    }

    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
    ]);

    event(new Registered($user));

    $this->info("Admin user '$name' created successfully with email '$email'.");

    return 0;
})->describe('Create a new admin user');
