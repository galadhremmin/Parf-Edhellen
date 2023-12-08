<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\MailSettingRepository;
use App\Models\{ 
    MailSetting,
    MailSettingOverride
};

class AccountNotificationController extends Controller
{
    private $_mailSettingRepository;

    public function __construct(MailSettingRepository $mailSettingRepository)
    {
        $this->_mailSettingRepository = $mailSettingRepository;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $settings = MailSetting::firstOrCreate([
            'account_id' => $user->id
        ]);
        $overrides = MailSettingOverride::forAccount($user)
            ->with('entity')->get();
        $events = $this->getEvents();

        return view('account.notification.index', [
            'settings'  => $settings,
            'overrides' => $overrides,
            'events'    => $events,
            'email'     => $user->email,
            'user'      => $user
        ]);
    }

    public function store(Request $request)
    {
        $allEvents = $this->getEvents();
        $rules = [];

        foreach ($allEvents as $event) {
            $rules[$event] = 'sometimes|boolean';
        }

        $events = $request->validate($rules);
        $disabledEvents = array_diff($allEvents, array_keys($events));
        foreach ($disabledEvents as $event) {
            $events[$event] = 0;
        }

        MailSetting::firstOrCreate([
            'account_id' => $request->user()->id
        ])->update($events);

        return redirect()->route('notifications.index');
    }

    public function deleteOverride(Request $request, string $entityType, int $entityId)
    {
        $accountId = $request->user()->id;

        MailSettingOverride::where([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'account_id' => $accountId
        ])->delete();

        return redirect()->route('notifications.index');
    }

    public function handleCancellationToken(Request $request, string $token)
    {
        $ok = is_string($token) && $this->_mailSettingRepository->handleCancellationToken($token);
        return view($ok ? 'account.notification.override-ok' : 'account.notification.override-error');
    }

    private function getEvents()
    {
        return [
            'forum_post_created',
            'forum_contribution_approved',
            'forum_contribution_rejected',
            'forum_posted_on_profile'
        ];
    }
}
