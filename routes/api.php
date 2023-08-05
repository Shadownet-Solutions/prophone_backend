<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PhoneController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\CampaignController;
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

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/completeSignIn', [AuthController::class, 'completeSignIn']);
Route::get('/user', [UserController::class, 'userProfile']);
Route::patch('/updateUser', [UserController::class, 'updateUser']);
Route::get('/username', [UserController::class, 'checkUsername']);
Route::get('/users', [UserController::class, 'index']);

//Route for phone number provisioning
Route::post('/provision', [PhoneController::class, 'provision']);

//get numbers
Route::get('/numbers', [PhoneController::class, 'numbers']);
//get inbox with number id
Route::get('/inbox/{number_id}', [PhoneController::class, 'inbox']);
//send message
Route::post('/send', [PhoneController::class, 'send']);
//get a sigle message conversation
Route::post('/conversation', [PhoneController::class, 'conversation']);
//get details of a contact
Route::get('/contact/{id}', [PhoneController::class, 'contact']);
//get dashboard data
Route::get('/analytics', [PhoneController::class, 'analytics']);

//contacts
Route::get('/contacts/{workspace}', [PhoneController::class, 'contacts']);


//get workspace team
Route::get('/team', [WorkspaceController::class, 'getTeamMembers']);

// write note
Route::post('/note', [PhoneController::class, 'add_note']);

//get campaigns
Route::get('/campaigns/{workspace}', [CampaignController::class, 'index']);