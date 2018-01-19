<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\{ 
    Account,
    MailSetting,
    MailSettingOverride
};
use Lang;

class MailSettingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $settings = MailSetting::firstOrCreate([
            'account_id' => $user->id
        ]);
        $overrides = MailSettingOverride::forAccount($user)
            ->with('entity')->get();
        $events = $this->getEvents();

        return view('mail-setting.index', [
            'settings'  => $settings,
            'overrides' => $overrides,
            'events'    => $events,
            'email'     => $user->email
        ]);
    }

    public function create(Request $request)
    {
        return view('inflection.create');
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

        return redirect()->route('mail-setting.index');
    }

    public function destroy(Request $request, int $id) 
    {
        $inflection = Inflection::findOrFail($id);
        
        foreach ($inflection->sentence_fragment_associations as $association) {
            $association->delete();
        }

        $inflection->delete();

        event(new InflectionDestroyed($inflection));

        return redirect()->route('inflection.index');
    } 

    public function handleCancellationToken(Request $request, string $token)
    {
        return $token;
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
