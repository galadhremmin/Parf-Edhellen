<?php

namespace App\Adapters;
            
use Illuminate\Support\Collection;

use App\Helpers\{
    LinkHelper,
    StorageHelper
};
use App\Repositories\AuditTrailRepository;
use App\Models\{
    Account,
    AuditTrail,
    Contribution,
    FlashcardResult,
    ForumPost,
    Gloss,
    Sentence
};

class AuditTrailAdapter
{
    private $_link;
    private $_repository;
    private $_storageHelper;

    public function __construct(LinkHelper $linkHelper, AuditTrailRepository $repository, StorageHelper $storageHelper)
    {
        $this->_link = $linkHelper;
        $this->_repository = $repository;
        $this->_storageHelper = $storageHelper;
    }

    /**
     * Transforms the specified collection of audit trail actions into an associative array 
     * containing strings a human would be able to understand.
     *
     * @param Collection|array $actions
     * @return array
     */
    public function adapt($actions)
    {
        $trail = [];
        foreach ($actions as $action) {
            $message = null;
            $entity = null;

            if ($action->entity instanceof Gloss) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_GLOSS_ADD:
                        $message = 'added the gloss';
                        break;
                    case AuditTrail::ACTION_GLOSS_EDIT:
                        $message = 'changed the gloss';
                        break;
                    case AuditTrail::ACTION_GLOSS_DELETE:
                        $message = 'deleted the gloss';
                        break;
                }

                $entity = '<a href="'.$this->_link->gloss($action->entity_id).'">' . 
                    $action->entity_name . '</a>';

            } else if ($action->entity instanceof Sentence) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_SENTENCE_ADD:
                        $message = 'added the phrase';
                        break;
                    case AuditTrail::ACTION_SENTENCE_EDIT:
                        $message = 'changed the phrase';
                        break;
                    case AuditTrail::ACTION_SENTENCE_DELETE:
                        $message = 'deleted the phrase';
                        break;
                }

                $entity = '<a href="'.$this->_link->sentence($action->entity->language_id, $action->entity->language->name,
                    $action->entity->id, $action->entity->name).'">' . $action->entity_name . '</a>';

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
                    case AuditTrail::ACTION_PROFILE_MERGED:
                        $message = 'account merged with '.$action->entity_name;
                        break;
                    case AuditTrail::ACTION_PROFILE_CHANGED_PASSWORD:
                        $message = 'changed password';
                        break;
                    case AuditTrail::ACTION_PROFILE_FORGOT_PASSWORD:
                        $message = 'requested a password reset link';
                        break;
                    case AuditTrail::ACTION_PROFILE_RESET_PASSWORD:
                        $message = 'reset their password';
                        break;
                    case AuditTrail::ACTION_ACCOUNT_ADD_ROLE:
                        $message = 'added the role '.json_decode($action->data)->role;
                        break;
                    case AuditTrail::ACTION_ACCOUNT_REMOVE_ROLE:
                        $message = 'removed the role '.json_decode($action->data)->role;
                        break;
                }

                if ($action->entity_id !== $action->account_id) {
                    $entity = 'for <a href="'.
                        route('account.edit', ['account' => $action->entity]).'">'.
                        $action->entity_name.
                    '</a>';
                }

            } else if ($action->entity instanceof ForumPost) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_COMMENT_ADD:
                        $message = 'posted';
                        break;
                    case AuditTrail::ACTION_COMMENT_EDIT:
                        $message = 'changed a post';
                        break;
                    case AuditTrail::ACTION_COMMENT_LIKE:
                        $message = 'liked a post';
                        break;
                }

                $entity = 'in <a href="'.
                    route('api.discuss.resolve', [
                        'entityType' => $action->entity_type,
                        'entityId' => $action->entity_id
                    ]).'">'.
                    $action->entity_name.
                '</a>';
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
            } else if ($action->entity instanceof Contribution) {
                switch ($action->action_id) {
                    case AuditTrail::ACTION_CONTRIBUTION_APPROVE:
                        $message = 'approved the contribution';
                        break;
                    case AuditTrail::ACTION_CONTRIBUTION_REJECT:
                        $message = 'rejected the contribution';
                        break;
                }

                $entity = '<a href="'.
                    route('contribution.show', [
                        'contribution' => $action->entity_id
                    ]).'">'.
                    $action->entity_name.
                '</a>';
            }

            // Some messages might be deliberately hidden or not yet supported -- skip them
            if ($message === null) {
                continue;
            }

            $item = [
                'account_id'     => $action->account_id,
                'account_name'   => $action->account->nickname,
                'account_avatar' => $this->_storageHelper->accountAvatar($action->account, true /* = _null_ if none exists */),
                'created_at'     => $action->created_at,
                'message'        => $message,
                'entity'         => $entity
            ];

            $trail[] = $item;
        }

        return $trail;
    }

    /**
     * Adapts the specified collection of audit trail actions, and merges similar actions (such as the user
     * repetitively editing the same entity). This method might reach out to the repository in order to 
     * compensate for the number of rows merged.
     *
     * @param Collection $actions
     * @param int $skipNoOfRows
     * @param array $previousItem
     * @return array
     */
    public function adaptAndMerge(Collection $actions, int $skipNoOfRows = 0, $previousItem = null)
    {
        $unmergedTrail = $this->adapt($actions);
        $trail = [];
        foreach ($unmergedTrail as $item) {
            // merge equivalent audit trail items to avoid spamming the log with the same message.
            if ($previousItem !== null && 
                $previousItem['account_id'] === $item['account_id'] &&
                $previousItem['message'] === $item['message'] &&
                $previousItem['entity'] === $item['entity']) {

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
        $noOfRows = $actions->count();
        $noOfMergers = $noOfRows - count($trail); 
        if ($noOfMergers > 0) {
            $remainingRows = $this->_repository->get($noOfMergers, $skipNoOfRows + $noOfRows);
            return array_merge($trail, $this->adapt($remainingRows));
        }

        return $trail;
    }
}