<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::insert([
            [
                'name' => 'Перминов Илья',
                'email' => 'ivelini@yandex.ru',
                'password' => bcrypt('73125478'),
                'type' => User::$ADMIN
            ],
            [
                'name' => 'Брусенцев Денис',
                'email' => '89222333310@mail.ru',
                'password' => bcrypt('20593799'),
                'type' => User::$ADMIN
            ],
            [
                'name' => 'Мастер',
                'email' => 'master@stan2000.ru',
                'password' => bcrypt('658452145'),
                'type' => User::$MASTER
            ]
        ]);
    }
}
