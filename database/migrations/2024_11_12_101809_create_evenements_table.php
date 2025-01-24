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
        Schema::disableForeignKeyConstraints();
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('ref_user');
            $table->unsignedBigInteger('typeEvenement_id');
            $table->string('description');
            $table->foreign('typeEvenement_id')->references('id')->on('type_evenements');
            $table->string('ref_typeEvenement');
            $table->date('date');
            $table->boolean('validated')->default(false);
            $table->boolean('isfinished')->default(false);
            $table->tinyInteger('index');
            $table->string('nom');
            $table->string('prenom');
            $table->string('email');
            $table->double('montant');
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
