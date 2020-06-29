<?php

// Public unrestricted API
Route::group([ 
    'namespace' => API_NAMESPACE, 
    'prefix'    => API_PATH.'/games'
], function () {
    Route::get('word-finder/{languageId}', 'WordFinderApiController@index')
    ->where([ 'languageId' => REGULAR_EXPRESSION_NUMERIC ]);
});
