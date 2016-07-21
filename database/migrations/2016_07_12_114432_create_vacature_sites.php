<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVacatureSites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacature_sites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vacature_sites_lijst');
            $table->string('url');
            $table->date('date_added');
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vacature_sites');
    }
}
