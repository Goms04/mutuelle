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
        Schema::create('user_evenements', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref');
            $table->string('ref_user_em');
            $table->string('ref_user_dest');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('ref_evenement');
            $table->unsignedBigInteger('evenement_id');
            $table->foreign('evenement_id')->references('id')->on('evenements');
            $table->string('ref_typeEvenement');
            $table->unsignedBigInteger('typeEvenement_id');
            $table->foreign('typeEvenement_id')->references('id')->on('type_evenements');
            $table->double('montant');
            $table->string('description');
            $table->string('nom_em');
            $table->string('prenom_em');
            $table->string('email_em');
            $table->string('nom_dest');
            $table->string('prenom_dest');
            $table->string('email_dest');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_evenements');
    }
};
