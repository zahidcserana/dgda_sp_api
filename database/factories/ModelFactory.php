<?php
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/


$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'user_name'     => $faker->name,
        'user_email'    => $faker->unique()->email,
        'password' => Hash::make('12345'),
        'pharmacy_shop_row_id' => 1,
        'userid' => 1000001
    ];
});