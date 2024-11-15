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
            'name' => 'Batam',
            'currency' => 'IDR',
            'tax_rate' => 0,
            'address' => 'Ruko Grand Niaga MasBlok B No. 53 Batam Centre',
            'description' => 'Flagship Shop',
            'is_active' => 1,
        ]);
    }
}
