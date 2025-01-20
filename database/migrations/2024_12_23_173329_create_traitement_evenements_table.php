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
        Schema::create('traitement_evenements', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref');
            $table->unsignedBigInteger('evenement_id');
            $table->foreign('evenement_id')->references('id')->on('evenements');
            $table->string('evenement_ref');
            $table->boolean('isfinished');
            $table->boolean('isvalidated');
            $table->string('message')->nullable();
            $table->tinyInteger('index');
            $table->dateTime('date');
            $table->unsignedBigInteger('traite_par');
            $table->foreign('traite_par')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traitement_evenements');
    }
};
