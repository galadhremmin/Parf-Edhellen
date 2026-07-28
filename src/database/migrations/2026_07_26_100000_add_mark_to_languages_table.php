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
        Schema::table('languages', function (Blueprint $table) {
            // Superscript prefix Eldamo uses in front of a word/root to mark it as belonging to
            // an earlier reconstruction period, e.g. "M" for Middle Primitive Elvish (ᴹ√GALAD),
            // "E" for Early Primitive Elvish (ᴱ√KALA). NULL means no prefix is shown.
            $table->string('mark', 8)->nullable()->after('short_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('mark');
        });
    }
};
