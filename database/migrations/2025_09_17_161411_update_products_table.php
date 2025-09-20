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
        Schema::table('products', function (Blueprint $table) {
            // Supprimer les colonnes obsolètes
            $table->dropColumn(['description', 'prix_unitaire', 'stock']);
            
            // Ajouter la colonne categorie
            $table->enum('categorie', ['Marbre', 'Carrelage', 'Autre'])->after('nom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Recréer les colonnes supprimées
            $table->text('description')->nullable();
            $table->decimal('prix_unitaire', 10, 2);
            $table->integer('stock')->default(0);
            
            // Supprimer la colonne categorie
            $table->dropColumn('categorie');
        });
    }
};
