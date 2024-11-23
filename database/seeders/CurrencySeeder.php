<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('currencies')->insert([
            'name' => 'SGD'
        ]);
        DB::table('currencies')->insert([
            'name' => 'IDR'
        ]);
        DB::table('currencies')->insert([
            'name' => 'CNY'
        ]);
        DB::table('currencies')->insert([
            'name' => 'USD'
        ]);
    }
}
