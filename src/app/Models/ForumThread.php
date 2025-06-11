<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ForumThread extends ModelBase implements Interfaces\IHasFriendlyName
{
    protected $fillable = [
        'entity_type', 'entity_id', 'subject', 'account_id',
        'number_of_posts', 'number_of_likes', 'normalized_subject',
        'is_sticky', 'forum_group_id', 'is_empty',
    ];

    use Traits\HasAccount;

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function forum_group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class);
    }

    public function forum_posts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function getFriendlyName()
    {
        return $this->subject;
    }

    public function scopeInGroup($query, int $id)
    {
        $query->where([
            ['number_of_posts', '>', 0],
            ['forum_group_id', $id],
        ]);
    }
}
