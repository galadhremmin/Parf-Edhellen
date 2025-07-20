<?php

namespace App\Models;

use App\Models\Initialization\Morphs;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contribution extends ModelBase implements Interfaces\IHasFriendlyName
{
    use SoftDeletes;
    use Traits\HasAccount;

    protected $fillable = [
        'account_id',
        'language_id',
        'lexical_entry_id',
        'sentence_id',
        'word',
        'payload',
        'keywords',
        'notes',
        'sense',
        'type',
        'approved_as_entity_id',
        'dependent_on_contribution_id',
        'reviewed_by_account_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'date_reviewed',
    ];

    public function reviewed_by(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'reviewed_by_account_id');
    }

    public function entity(): BelongsTo
    {
        $modelName = Morphs::getMorphedModel($this->type);

        return $this->belongsTo($modelName, 'approved_as_entity_id', 'id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(Contribution::class, 'dependent_on_contribution_id', 'id');
    }

    public function dependent_on(): BelongsTo
    {
        return $this->belongsTo(Contribution::class, 'dependent_on_contribution_id', 'id');
    }

    public function lexical_entry(): BelongsTo
    {
        return $this->belongsTo(LexicalEntry::class, 'lexical_entry_id');
    }

    public function scopeWhereAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function getFriendlyName()
    {
        return $this->word;
    }
}
