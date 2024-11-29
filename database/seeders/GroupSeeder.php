<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\warehouse;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('app_groups')->insert([
            'name' => 'Main Group',
            'currency' => 'SGD',
            'tax_rate' => 0,
            'address' => 'a fake address',
            'description' => 'Main group',
            'is_active' => 1,
        ]);
    }
}
