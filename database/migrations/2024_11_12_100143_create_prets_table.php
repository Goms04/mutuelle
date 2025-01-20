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
        Schema::create('prets', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref');
            $table->integer('montant');
            $table->date('date_pret');
            $table->string('motif_pret');
            $table->boolean('mode_remboursement');
            $table->boolean('soldout')->default(0);
            $table->boolean('prerequis')->default(0);
            $table->boolean('isfinished')->default(0);
            $table->double('montant_remboursement');
            $table->integer('duree');
            $table->string('marge_totale')->nullable();
            $table->double('quotite_cessible')->nullable();
            $table->boolean('validated')->default(0);
            $table->tinyInteger('index')->default(0);
            $table->double('montant_accorde')->nullable();
            $table->integer('duree_remboursement_accorde')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prets');
    }
};
