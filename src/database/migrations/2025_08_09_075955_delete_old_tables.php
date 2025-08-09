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
        Schema::dropIfExists('old_translation_versions');
        Schema::dropIfExists('old_gloss_versions');
        Schema::dropIfExists('old_translations');
        Schema::dropIfExists('old_glosses');
        Schema::dropIfExists('old_gloss_groups');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Point of no return.
    }
};
