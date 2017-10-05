<?php

namespace App\Repositories;

use App\Models\{ Account, Contribution, ForumContext, ForumPost, ForumPostLike, Translation, Sentence };
use Illuminate\Support\Facades\DB;

class ForumRepository
{
    public function getCommentCountForEntities(int $contextId, array $ids)
    {
        $result = ForumPost::where([
                ['forum_context_id', $contextId],
                ['is_hidden', 0],
                ['is_deleted', 0]
            ])
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
            ->select('id', 'is_elevated', 'friendly_name')
            ->firstOrFail();
        $entity = null;
        $entityName = null;

        switch ($context->id) {
            case ForumContext::CONTEXT_TRANSLATION:
                $entity = Translation::findOrFail($id);
                $entityName = $entity->word->word;
                break;
            
            case ForumContext::CONTEXT_SENTENCE:
                $entity = Sentence::findOrFail($id);
                $entityName = $entity->name;
                break;

            case ForumContext::CONTEXT_ACCOUNT:
                $entity = Account::findOrFail($id);
                $entityName = $entity->nickname;
                break;

            case ForumContext::CONTEXT_CONTRIBUTION:
                $entity = Contribution::findOrFail($id);
                $entityName = $entity->word;
                break;
                
            default:
                return null;
        }

        return [
            'id'            => $context->id,
            'is_elevated'   => $context->is_elevated,
            'friendly_name' => $context->friendly_name,
            'entity_name'   => $entityName,
            'entity'        => $entity
        ];
    }
}
