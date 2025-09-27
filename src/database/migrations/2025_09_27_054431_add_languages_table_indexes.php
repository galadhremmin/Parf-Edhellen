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
        Schema::table('languages', function (Blueprint $table) {
            // Add composite index for ordering by order, then name
            // This will significantly improve performance for getLanguages() calls
            $table->index(['order', 'name'], 'languages_order_name_index');
            
            // Add index on category for grouping operations
            $table->index('category', 'languages_category_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropIndex('languages_order_name_index');
            $table->dropIndex('languages_category_index');
        });
    }
};