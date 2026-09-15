<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Uncategorised API failures were saved as "frontend-" and carry too little to act on.
        DB::table('system_errors')
            ->where('category', 'frontend-')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deleted rows cannot be restored.
    }
};
