<?php

// Public unrestricted API
Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH.'/games'
], function () {
    Route::get('word-finder/{gameId}', 'WordFinderApiController@play')
        ->where([ 'gameId' => REGULAR_EXPRESSION_NUMERIC ]);
});
