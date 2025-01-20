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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref');
            $table->string('nom');
            $table->string('prenom');
            $table->boolean('sexe');
            $table->date('date_naissance');
            $table->string('email')->unique();
            $table->unsignedBigInteger('poste_id');
            $table->foreign('poste_id')->references('id')->on('postes');
            $table->unsignedBigInteger('subdivision_id');
            $table->foreign('subdivision_id')->references('id')->on('subdivisions');
            $table->tinyInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->double('montant_a_cotiser')->nullable();
            $table->double('solde_initial')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->tinyInteger('index')->default(0);
            $table->boolean('deleted')->default(false);
            $table->boolean('enabled')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
