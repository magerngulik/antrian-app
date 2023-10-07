<?php

use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntreanController;
use App\Http\Controllers\AuthentificationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//authentification
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthentificationController::class, 'login']);
    Route::post('/register', [AuthentificationController::class, 'register']);
    Route::get('/logout', [
        AuthentificationController::class,
        'logout',
    ])->middleware('auth:sanctum');
    Route::get('/profile/{id}', [
        AuthentificationController::class,
        'me',
    ]);
    Route::get('get-user', [AuthentificationController::class, 'getUserAssignment'])->middleware('auth:sanctum');
});


//pick-queue user
Route::get('pick-queue/{id}', [AntreanController::class, 'pickQueue']);
Route::get('list-queue', [AntreanController::class, 'listQueue']);
Route::get('view-queue', [AntreanController::class, 'viewQueueList']);


//assignment
Route::get('get-role', [AntreanController::class, 'getRolesAssignment']);
Route::post('chose-assignment', [AntreanController::class, 'choseAssignment']);
Route::get('assignment/', [AntreanController::class, 'getAssignment']);
Route::get('assignment/{id}', [AntreanController::class, 'getAssignmentSingle']);
Route::delete('assignment/{id}', [AntreanController::class, 'deleteAssignment']);

//code queue
Route::get('code-queue', [AntreanController::class, 'codeQueue']);
Route::get('code-queue/{id}', [AntreanController::class, 'codeQueueId']);
Route::post('code-queue', [AntreanController::class, 'createCodeQueue']);
Route::put('code-queue/{id}', [AntreanController::class, 'updateCodeQueue']);
Route::delete('code-queue/{id}', [AntreanController::class, 'deleteCodeQueue']);

//generate code costumer
Route::get('costumer-queue', [AntreanController::class, 'costumerQueue']);
Route::get('costumer-queue/{id}', [AntreanController::class, 'costumerQueueCreate']);
Route::get('test-view-queue', [AntreanController::class, 'testViewQueue']);

Route::get('view-queue-user/{id}', [AntreanController::class, 'viewQueueUser']);
Route::get('confirm-queue-user/{id}', [AntreanController::class, 'confirmQueueUser']);
Route::get('skip-queue-user/{id}', [AntreanController::class, 'skipQueueUser']);
Route::get('recall-queue-user/{id}', [AntreanController::class, 'recallViewQueue']);
