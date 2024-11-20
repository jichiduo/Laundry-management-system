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
            'category' => 'Laundry'
        ]);
        DB::table('types')->insert([
            'name' => 'Pants',
            'category' => 'Laundry'
        ]);
        DB::table('types')->insert([
            'name' => 'Large Size',
            'category' => 'Laundry'
        ]);
        DB::table('types')->insert([
            'name' => 'Others',
            'category' => 'Laundry'
        ]);
        DB::table('types')->insert([
            'name' => 'Cash',
            'category' => 'Payment',
        ]);
        DB::table('types')->insert([
            'name' => 'QR Code',
            'category' => 'Payment',
        ]);
        DB::table('types')->insert([
            'name' => 'Member Card',
            'category' => 'Payment',
        ]);
    }
}
