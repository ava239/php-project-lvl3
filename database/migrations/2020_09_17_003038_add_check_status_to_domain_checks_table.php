<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckStatusToDomainChecksTable extends Migration
{
    public function up()
    {
        Schema::table('domain_checks', function (Blueprint $table) {
            $table->string('check_status')->default('ok');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('domain_checks', function (Blueprint $table) {
            $table->dropColumn('check_status');
        });
    }
}
