<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Akun Awal
    |--------------------------------------------------------------------------
    |
    | Password tiga akun bersama yang dibuat oleh DatabaseSeeder, dibaca dari
    | environment agar tidak ada password teks polos di kode.
    |
    */

    'passwords' => [
        'admin' => env('SEED_ADMIN_PASSWORD'),
        'hr' => env('SEED_HR_PASSWORD'),
        'panitia' => env('SEED_PANITIA_PASSWORD'),
    ],

];
