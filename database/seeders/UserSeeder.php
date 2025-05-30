<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            "name" => "teacher",
            "email" => "teacher@gmail.com",
            "password" => bcrypt("jangandiganti"),
            "role" => "teacher",
        ]);

        User::create([
            "name" => "student",
            "email" => "student@gmail.com",
            "password" => bcrypt("jangandiganti"),
            "role" => "student",
        ]);
    }
}
