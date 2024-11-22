<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('divisions')->insert([
            'name' => 'Batam Centre Shop',
            'address' => 'Ruko Grand Niaga MasBlok B No. 53 Batam Centre',
            'tel' => '0778-511811',
            'remark' => 'Batam Centre shop',
            'group_id' => 1,
            'group_name' => 'Batam Group',
        ]);
    }
}
