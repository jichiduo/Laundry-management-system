<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Str::random(10)
        DB::table('app_users')->insert([
            'user_id' => 1,
            'division_id' => 1,
            'group_id' => 1,
        ]);
    }
}
