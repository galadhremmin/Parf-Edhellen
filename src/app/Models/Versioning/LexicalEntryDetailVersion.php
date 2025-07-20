<?php

namespace App\Models\Versioning;

use App\Models\ModelBase;
use App\Models\Traits\HasAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LexicalEntryDetailVersion extends ModelBase
{
    use HasAccount;

    protected $table = 'lexical_entry_detail_versions';

    protected $fillable = [
        'lexical_entry_version_id', 'category', 'text', 'order', 'type',
    ];

    public function lexical_entry_version(): BelongsTo
    {
        return $this->belongsTo(LexicalEntryVersion::class, 'lexical_entry_version_id');
    }
}
