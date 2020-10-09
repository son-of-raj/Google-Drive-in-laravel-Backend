<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/', function () {
    return view('welcome');
});
Route::get('put', 'DriveController@upload'); 
Route::get('local', 'DriveController@UploadLocal');
Route::post('put-existing', 'DriveController@putexisting'); 
Route::get('list', 'DriveController@listing');
Route::get('folder', 'DriveController@folder');
Route::get('list-folder-contents', 'DriveController@foldercontents');
Route::get('get', 'DriveController@get');
Route::get('put-get-stream', 'DriveController@largerfile');
Route::get('create-dir', 'DriveController@createDirectory');
Route::get('create-sub-dir', 'DriveController@CreateSubDirectory');
Route::get('put-in-dir', 'DriveController@PutInDirectory');
Route::get('newest', 'DriveController@newest');
Route::get('delete', 'DriveController@delete');
Route::get('rename-dir', 'DriveController@RenameDirectory');
Route::get('share', 'DriveController@share');
Route::get('export/{basename}', 'DriveController@export');