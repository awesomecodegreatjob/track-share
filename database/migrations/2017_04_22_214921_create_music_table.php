<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('music', function (Blueprint $table) {
            $table->increments('id');

            $table->string('key')->unique();
            $table->string('band');
            $table->string('album');
            $table->string('track')->nullable();
            $table->string('image_url');
            $table->string('google_music_url')->nullable();
            $table->string('spotify_url')->nullable();

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
        Schema::drop('music');
    }
}
