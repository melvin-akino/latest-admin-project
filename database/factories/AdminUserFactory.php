<?php
use Faker\Generator as Faker;
$factory->define(App\Models\AdminUser::class, function (Faker $faker) {
    $autoname = strtolower('ADM' . $faker->randomNumber(5, true));
    return [
        'name' => $autoname,
        'email' => $autoname.'@npt.com',
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
    ];
});
