<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('file')->unique();
            $table->string('name');
            $table->string('artist');
            $table->string('remix')->nullable();
            $table->integer('year')->nullable();
            $table->string('genre')->nullable();
			$table->integer('is_current')->default(0);
			$table->integer('is_name_found')->default(0); 	// no more name accepted
			$table->integer('is_artist_found')->default(0); // no more artist accepted
			$table->integer('is_remix_found')->default(0);// no more remixer accepted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
