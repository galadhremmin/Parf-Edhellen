<?php

use App\Models\Language;
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
        Language::where('category', 'Late Period (1950-1973)')
            ->update(['is_unusual' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
