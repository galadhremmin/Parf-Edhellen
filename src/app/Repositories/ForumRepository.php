<?php

namespace App\Repositories;

use App\Models\{ ForumContext, ForumPost, ForumPostLike, Translation, Sentence };
use Illuminate\Support\Facades\DB;

class ForumRepository
{
    public function getCommentCountForEntities(int $contextId, array $ids)
    {
        $result = ForumPost::where('forum_context_id', $contextId)
            ->whereIn('entity_id', $ids)
            ->groupBy('entity_id')
            ->select('entity_id', DB::raw('count(*) as count'))
            ->get();

        $groupedResult = [];
        foreach ($result as $r) {
            $groupedResult[$r->entity_id] = $r->count;
        }

        return $groupedResult;
    }

    public function getContext(string $contextName, int $id)
    {
        // Retrieve the context
        $context = ForumContext::where('name', $contextName)
            ->select('id')
            ->firstOrFail();
        $entity = null;

        switch ($context->id) {
            case ForumContext::CONTEXT_TRANSLATION:
                $entity = Translation::active()
                    ->where('id', $id)
                    ->firstOrFail()
                    ->getOrigin();
                break;
            
            case ForumContext::CONTEXT_SENTENCE:
                $entity = Sentence::findOrFail($id);
                break;

            default:
                return null;
        }

        return [
            'id'     => $context->id,
            'entity' => $entity
        ];
    }
}
