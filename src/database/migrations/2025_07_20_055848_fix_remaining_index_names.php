<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix remaining index names that still contain old table name references
        
        // Fix glosses table index (remove 'new_' prefix)
        DB::statement('ALTER TABLE glosses RENAME INDEX new_glosses_lexical_entry_id_index TO glosses_lexical_entry_id_index');
        
        // Fix gloss_versions table index (remove 'new_' prefix)
        DB::statement('ALTER TABLE gloss_versions RENAME INDEX new_gloss_versions_lexical_entry_version_id_foreign TO gloss_versions_lexical_entry_version_id_foreign');
        
        // Fix lexical_entry_inflections table indexes (remove 'gloss_inflections_' prefix)
        DB::statement('ALTER TABLE lexical_entry_inflections RENAME INDEX gloss_inflections_sentence_fragment_id_index TO lexical_entry_inflections_sentence_fragment_id_index');
        DB::statement('ALTER TABLE lexical_entry_inflections RENAME INDEX gloss_inflections_inflection_group_uuid_index TO lexical_entry_inflections_inflection_group_uuid_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the index renames
        DB::statement('ALTER TABLE glosses RENAME INDEX glosses_lexical_entry_id_index TO new_glosses_lexical_entry_id_index');
        DB::statement('ALTER TABLE gloss_versions RENAME INDEX gloss_versions_lexical_entry_version_id_foreign TO new_gloss_versions_lexical_entry_version_id_foreign');
        DB::statement('ALTER TABLE lexical_entry_inflections RENAME INDEX lexical_entry_inflections_sentence_fragment_id_index TO gloss_inflections_sentence_fragment_id_index');
        DB::statement('ALTER TABLE lexical_entry_inflections RENAME INDEX lexical_entry_inflections_inflection_group_uuid_index TO gloss_inflections_inflection_group_uuid_index');
    }
};
