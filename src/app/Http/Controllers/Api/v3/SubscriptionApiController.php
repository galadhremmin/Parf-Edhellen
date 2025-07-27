<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Initialization\Morphs;
use App\Repositories\MailSettingRepository;
use Illuminate\Http\Request;

class SubscriptionApiController extends Controller
{
    private MailSettingRepository $_mailSettingRepository;

    public function __construct(MailSettingRepository $mailSettingRepository)
    {
        $this->_mailSettingRepository = $mailSettingRepository;
    }

    public function getSubscriptionForEntity(Request $request, string $morph, int $id)
    {
        $entity = $this->loadEntity($morph, $id);

        return [
            'subscribed' => $this->_mailSettingRepository->getOverride($request->user()->id, $entity) ?? false,
        ];
    }

    public function subscribeToEntity(Request $request, string $morph, int $id)
    {
        $entity = $this->loadEntity($morph, $id);

        return [
            'subscribed' => $this->_mailSettingRepository->setNotifications($request->user()->id, $entity, true),
        ];
    }

    public function unsubscribeFromEntity(Request $request, string $morph, int $id)
    {
        $entity = $this->loadEntity($morph, $id);

        return [
            'subscribed' => $this->_mailSettingRepository->setNotifications($request->user()->id, $entity, null),
        ];
    }

    private function loadEntity(string $morph, int $id)
    {
        $entityType = Morphs::getMorphedModel($morph);
        if ($entityType === null) {
            return response(sprintf('Incorrect or unrecognised morph %s.', $morph), 400);
        }

        return ($entityType)::findOrFail($id);
    }
}
