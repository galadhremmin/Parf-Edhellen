<?php

namespace App\Http\Controllers\Contributions;

use App\Models\LexicalEntry;
use App\Models\LexicalEntryInflection;
use App\Models\Initialization\Morphs;
use App\Models\Sentence;
use Illuminate\Http\Request;

class ContributionControllerFactory
{
    /**
     * Invokes the closure associated with the morph's model name. The morph can be passed
     * as a string, or it can be identified from a request's input parameters.
     *
     * @param [string|Request] $morphOrRequest
     * @param  array  $cases
     */
    public static function createController($morphOrRequest): IContributionController
    {
        $morph = ($morphOrRequest instanceof Request)
            ? $morphOrRequest->input('morph')
            : $morphOrRequest;

        $modelName = Morphs::getMorphedModel($morph);

        $controllerName = null;
        switch ($modelName) {
            case LexicalEntry::class:
                $controllerName = LexicalEntryContributionController::class;
                break;
            case LexicalEntryInflection::class:
                $controllerName = GlossInflectionContributionController::class;
                break;
            case Sentence::class:
                $controllerName = SentenceContributionController::class;
                break;
            default:
                throw new \Exception('Unrecognised model name "'.$modelName.'". Ensure that the entity\'s morph is supported by the ContributionControllerFactory.');
        }

        return resolve($controllerName);
    }
}
