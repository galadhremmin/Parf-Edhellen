<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaHelper
{
    public static function createAssessment(?string $token, string $action): bool
    {
        if (! config('ed.recaptcha.sitekey')) {
            return true; // we'll assume the token is valid if the recaptcha configuration is not set
        }

        if (empty($token)) {
            return false; // since recaptcha is enabled in this case the token is required and invalid if it is empty
        }

        $assessmentUrl = sprintf('https://recaptchaenterprise.googleapis.com/v1/projects/%s/assessments?key=%s', // 
            urlencode(config('ed.recaptcha.project')),
            urlencode(config('ed.recaptcha.key'))
        );
        $data = [
            'event' => [
                'siteKey' => config('ed.recaptcha.sitekey'),
                'token' => $token,
                'expectedAction' => $action,
            ],
        ];

        try {
            $request = Http::post($assessmentUrl, $data);
            $response = $request->json();
            return $response['tokenProperties']['valid'] ?? false;
        } catch (\Exception $e) {
            Log::error('RecaptchaHelper::createAssessment failed: ' . $e->getMessage());
            return true; // we'll assume the token is valid if the request fails
        }
    }
}
