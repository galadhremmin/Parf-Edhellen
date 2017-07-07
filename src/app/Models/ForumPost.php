<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    protected $fillable = [ 'context_id', 'entity_id', 'parent_form_post_id', 'number_of_likes', 'account_id', 'content' ];
    
    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function likes() {
        return $this->hasMany(ForumPostLike::class);
    }
}
