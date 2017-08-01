<?php

namespace App\Repositories;

use App\Helpers\LinkHelper;
use App\Models\{ Account, AuditTrail, Favourite, FlashcardResult, ForumContext, ForumPost, Sentence, Translation };

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\Relation;

class AuditTrailRepository
{
    protected $_link;

    public function __construct(LinkHelper $link)
    {
        $this->_link = $link;
    }

    public static function mapMorps() 
    {
        Relation::morphMap([
            'account'     => Account::class,
            'favourite'   => Favourite::class,
            'forum'       => ForumPost::class,
            'sentence'    => Sentence::class,
            'translation' => Translation::class,
            'flashcard'   => FlashcardResult::class
        ]);
    }

    public function get(int $numberOfRows)
    {
        $actions = AuditTrail::orderBy('id', 'desc')
            ->with([
                'account' => function ($query) {
                    $query->select('id', 'nickname');
                },
                'entity' => function () {}
            ])
            ->take(10)
            ->get();

        $trail = [];
        foreach ($actions as $action) {
            $message = null;
            $entity = null;

            if ($action->entity instanceof Translation) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_TRANSLATION_ADD:
                        $message = 'added the gloss';
                        break;
                    case AuditTrail::ACTION_TRANSLATION_EDIT:
                        $message = 'changed the gloss';
                        break;
                }

                $entity = '<a href="'.$this->_link->translation($action->entity->id).'">' . 
                    $action->entity->word->word . '</a>';

            } else if ($action->entity instanceof Sentence) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_SENTENCE_ADD:
                        $message = 'added the phrase';
                        break;
                    case AuditTrail::ACTION_SENTENCE_EDIT:
                        $message = 'changed the phrase';
                        break;
                }

                $entity = '<a href="'.$this->_link->sentence($action->entity->language_id, $action->entity->language->name,
                    $action->entity->id, $action->entity->name).'">' . $action->entity->name . '</a>';

            } else if ($action->entity instanceof Account) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_PROFILE_FIRST_TIME:
                        $message = 'logged in for the first time';
                        break;
                    case AuditTrail::ACTION_PROFILE_EDIT:
                        $message = 'changed their profile';
                        break;
                    case AuditTrail::ACTION_PROFILE_EDIT_AVATAR:
                        $message = 'changed their avatar';
                        break;
                }

            } else if ($action->entity instanceof ForumPost) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_COMMENT_ADD:
                        $message = 'wrote';
                        break;
                    case AuditTrail::ACTION_COMMENT_EDIT:
                        $message = 'modified';
                        break;
                    case AuditTrail::ACTION_COMMENT_LIKE:
                        $message = 'liked';
                        break;
                }

                $entity = '<a href="/api/v1/forum/'.$action->entity->id.'">a comment</a>';
            } else if ($action->entity instanceof FlashcardResult) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_FLASHCARD_FIRST_CARD:
                        $message = 'completed their first flashcard';
                        break;
                    case AuditTrail::ACTION_FLASHCARD_CARD_10:
                        $message = 'completed 10 flashcards';
                        break;
                    case AuditTrail::ACTION_FLASHCARD_CARD_50:
                        $message = 'completed 50 flashcards';
                        break;
                    case AuditTrail::ACTION_FLASHCARD_CARD_100:
                        $message = 'completed 100 flashcards';
                        break;
                    case AuditTrail::ACTION_FLASHCARD_CARD_200:
                        $message = 'completed 200 flashcards';
                        break;
                    case AuditTrail::ACTION_FLASHCARD_CARD_500:
                        $message = 'completed 500 flashcards';
                        break;
                }
            }

            // Some messages might be deliberately hidden or not yet supported -- skip them
            if ($message === null) {
                continue;
            }

            $trail[] = [
                'account_id'   => $action->account_id,
                'account_name' => $action->account->nickname,
                'created_at'   => $action->created_at,
                'message'      => $message,
                'entity'       => $entity
            ];
        }

        return $trail;
    }

    public function store(int $action, $entity, int $userId = 0)
    {
        if ($userId === 0) {
            // Is the user authenticated?
            if (! Auth::check()) {
                return;
            }

            $userId = Auth::user()->id;
        }

        // Retrieve the associated morph map key based on the specified entity.
        $typeName = null;
        $map = Relation::morphMap();
        foreach ($map as $name => $className) {
            if (is_a($entity, $className)) {
                $typeName = $name;
                break;
            }
        }

        if ($typeName === null) {
            throw new \Exception(get_class($entity).' is not supported.');
        }

        AuditTrail::create([
            'account_id'  => $userId,
            'entity_id'   => $entity->id,
            'entity_type' => $typeName,
            'action_id'   => $action
        ]);
    }
}
