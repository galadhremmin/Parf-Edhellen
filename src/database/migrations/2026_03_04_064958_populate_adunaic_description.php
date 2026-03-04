<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Language;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Language::where('short_name', 'ad')
            ->update(['description' => 'Adûnaic was the official language of the Númenoreans, the kingly denizens of Númenor.']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Language::where('short_name', 'ad')
            ->update(['description' => null]);
    }
};
