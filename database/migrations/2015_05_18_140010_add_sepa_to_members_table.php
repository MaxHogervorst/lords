<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSepaToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('iban')->nullable()->default(null);
            $table->string('bic')->nullable()->default(null);
            $table->boolean('had_collection')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('iban');
            $table->dropColumn('bic');
            $table->dropColumn('had_collection');
        });
    }
}
