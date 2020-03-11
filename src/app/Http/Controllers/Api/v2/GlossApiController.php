<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\GlossRepository;

class GlossApiController extends Controller
{
    private $_repository;

    public function __construct(GlossRepository $repository)
    {
        $this->_repository = $repository;
    }

    public function get(Request $request, int $id)
    {
        $glosses = $this->_repository->getGloss($id);
        if ($glosses->isEmpty()) {
            return response('', 404);
        }

        return [
            'gloss' => $glosses->first()
        ];
    }

    public function destroy(Request $request, int $id)
    {
        $data = $request->validate([
            'replacement_id' => 'numeric|not_in:'.$id
        ]);

        $replacementId = intval($data['replacement_id']);

        if ($replacementId !== 0) {
            $glosses = $this->_repository->getGlossVersion($replacementId);
            if ($glosses->count() === 0) {
                return response(null, 400);
            }
        }

        return response(null,
            $this->_repository->deleteGlossWithId($id, $replacementId) ? 200 : 400
        );
    }
}
