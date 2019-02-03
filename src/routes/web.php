<?php

$numericReg = '[0-9]+';
$urlSeoReg = '[a-z_\-0-9]+';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require_once 'constants.php';

require_once 'web-routes/authentication.php';
require_once 'web-routes/common.php';
require_once 'web-routes/dashboard-admin.php';
require_once 'web-routes/dashboard-user.php';
require_once 'web-routes/dictionary.php';
require_once 'web-routes/flashcards.php';
require_once 'web-routes/phrases.php';

require_once 'web-routes/api-admin.php';
require_once 'web-routes/api-public.php';
require_once 'web-routes/api-user.php';
require_once 'web-routes/discuss-api.php';

require_once 'web-routes/resources-admin.php';
require_once 'web-routes/resources-user.php';
require_once 'web-routes/resources.php';

// Mail cancellation
Route::get('/stop-notification/{token}', ['uses' => 'Resources\\MailSettingController@handleCancellationToken'])
    ->name('mail-setting.cancellation');

// Sitemap
Route::get('sitemap/{context}', 'SitemapController@index');

