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

    public function destroy(Request $request, int $id)
    {
        $data = $request->validate([
            'replacement_id' => 'numeric|exists:glosses,id|not_in:'.$id
        ]);

        $glosses = $this->_repository->getGloss($data['replacement_id']);
        if ($glosses->count() === 0) {
            return response(null, 400);
        }

        return response(null,
            $this->_repository->deleteGlossWithId($id, $glosses->first()->id) ? 200 : 400
        );
    }
}
