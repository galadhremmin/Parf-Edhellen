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

    public function get(int $noOfRows, int $skipNoOfRows = 0, $previousItem = null)
    {
        $query = AuditTrail::orderBy('id', 'desc')
            ->with([
                'account' => function ($query) {
                    $query->select('id', 'nickname');
                },
                'entity' => function () {}
            ]);
        
        if (! Auth::check() || ! Auth::user()->isAdministrator()) {
            // Put audit trail actions here that only administrators should see.
            $query = $query->where('is_admin', 0)
                ->whereNotIn('action_id', [
                    AuditTrail::ACTION_PROFILE_AUTHENTICATED
                ]);
        }
        
        $actions = $query->skip($skipNoOfRows)->take($noOfRows)->get();

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
                    case AuditTrail::ACTION_PROFILE_AUTHENTICATED:
                        $message = 'logged in';
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

            $item = [
                'account_id'   => $action->account_id,
                'account_name' => $action->account->nickname,
                'created_at'   => $action->created_at,
                'message'      => $message,
                'entity'       => $entity
            ];

            // merge equivalent audit trail items to avoid spamming the log with the same message.
            if ($previousItem !== null && 
                $previousItem['account_id'] === $item['account_id'] &&
                $previousItem['message'] === $item['message']) {

                // choose the latest item
                $trailLength = count($trail);
                if ($trailLength < 1 || $previousItem['created_at'] > $item['created_at']) {
                    // TODO: when $trailLength < 1, the $previousItem originated from the 'parent' invocation of this method, so we need to
                    // TODO: figure out how to replace the $previousItem with the $item, in order to ensure that the _latest_ item is selected.
                    //
                    // ^ -- this is not done at the moment.
                    continue;
                }

                $trail[$trailLength - 1] = $item;
            } else {
                $trail[] = $item;
            }

            $previousItem = $item;
        }

        // count the number of missing items to the list and attempt to populate the list
        // with remaining items.
        $noOfMergers = count($actions) - count($trail); 
        return $noOfMergers > 0 
            ? array_merge($trail, $this->get($noOfMergers, $skipNoOfRows + $noOfRows, $previousItem))
            : $trail;
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

        // check whether the specified user is an administrator
        $request = request();
        $admin = false;
        if ($request !== null) {
            $user = $request->user();
            $admin = $user !== null && $user->isIncognito();
        }

        AuditTrail::create([
            'account_id'  => $userId,
            'entity_id'   => $entity->id,
            'entity_type' => $typeName,
            'action_id'   => $action,
            'is_admin'    => $admin
        ]);
    }
}
