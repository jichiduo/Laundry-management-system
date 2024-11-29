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
            'name' => 'Centre Shop',
            'address' => 'a fake address',
            'tel' => '0778-511811',
            'remark' => 'Centre shop',
            'group_id' => 1,
            'group_name' => 'Main Group',
        ]);
    }
}
