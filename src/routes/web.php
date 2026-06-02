<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::redirect('/', '/admin');

Route::get('/admin/dev-login', function () {
    abort_unless(
        app()->environment(['local', 'testing']) && config('meterpipe.admin_dev_login_enabled'),
        404,
    );

    $email = (string) config('meterpipe.admin_dev_login_email');

    abort_if($email === '', 404);

    $user = User::query()->firstOrCreate(
        ['email' => $email],
        [
            'name' => 'Meterpipe Dev Admin',
            'password' => Hash::make(Str::random(40)),
        ],
    );

    Auth::login($user);

    return redirect('/admin');
})->name('admin.dev-login');
