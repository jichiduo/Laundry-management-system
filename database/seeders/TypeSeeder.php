<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('types')->insert([
            'name' => 'Washing Machine',
            'category' => 'Fix Asset'
        ]);
        DB::table('types')->insert([
            'name' => 'Cloth',
            'name' => 'Clothing'
        ]);
        DB::table('types')->insert([
            'name' => 'Pants',
            'name' => 'Clothing'
        ]);
        DB::table('types')->insert([
            'name' => 'Large Size',
            'name' => 'Clothing'
        ]);
        DB::table('types')->insert([
            'name' => 'Others',
            'name' => 'Clothing'
        ]);
        DB::table('types')->insert([
            'name' => 'Cash',
            'name' => 'Payment',
        ]);
        DB::table('types')->insert([
            'name' => 'QR Code',
            'name' => 'Payment',
        ]);
        DB::table('types')->insert([
            'name' => 'Member Card',
            'name' => 'Payment',
        ]);
    }
}
