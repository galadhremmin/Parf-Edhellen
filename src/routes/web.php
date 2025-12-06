<?php

use App\Http\Controllers\AccountNotificationController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

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

require 'web-routes/account-user.php';
require 'web-routes/authentication.php';
require 'web-routes/common.php';
require 'web-routes/author-user.php';
require 'web-routes/dictionary.php';
require 'web-routes/flashcards.php';
require 'web-routes/flashcards-user.php';
require 'web-routes/games.php';
require 'web-routes/phrases.php';
require 'web-routes/word-finder-admin.php';
require 'web-routes/word-finder.php';

require 'web-routes/resources-admin.php';
require 'web-routes/resources-reviewer.php';
require 'web-routes/resources-user.php';
require 'web-routes/resources.php';

// Mail cancellation
Route::get('/stop-notification/{token}', [AccountNotificationController::class, 'handleCancellationToken'])
    ->name('notifications.cancellation');

// Sitemap
Route::get('sitemap/{context}', [SitemapController::class, 'index']);
