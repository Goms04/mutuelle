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
        Schema::create('user_cotisations', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref');
            $table->string('ref_user');
            $table->string('ref_cotisation');
            $table->integer('mois');
            $table->integer('annee');
            $table->string('email');
            $table->string('nom');
            $table->string('prenom');
            $table->double('capital_brut')->default(0);
            $table->double('capital_net')->default(0);
            $table->double('montant_cotise');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('cotisation_id');
            $table->foreign('cotisation_id')->references('id')->on('cotisations');
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cotisations');
    }
};
