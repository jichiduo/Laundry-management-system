<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSequence extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create sequence and event to reset the sequence monthly
        DB::statement("CREATE SEQUENCE seq_job START WITH 1 INCREMENT BY 1");
        DB::statement("CREATE EVENT event_reset_job ON SCHEDULE EVERY '1' MONTH STARTS '2021-11-01 00:00:00'  DO ALTER SEQUENCE seq_job restart = 1");
        DB::statement("CREATE SEQUENCE seq_transaction START WITH 1 INCREMENT BY 1");
        DB::statement("CREATE EVENT event_reset_transaction ON SCHEDULE EVERY '1' MONTH STARTS '2021-11-01 00:00:00'  DO ALTER SEQUENCE seq_transaction restart = 1");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP SEQUENCE IF EXISTS seq_job");
        DB::statement("DROP EVENT IF EXISTS event_reset_job");
        DB::statement("DROP SEQUENCE IF EXISTS seq_transaction");
        DB::statement("DROP EVENT IF EXISTS event_reset_transaction");
    }
}
