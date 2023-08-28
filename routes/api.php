<?php
header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Accept, Content-Type, X-Auth-Token, Origin, Authorization');
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PhoneController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\AudienceController;
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
Route::post('socialLogin', [AuthController::class, 'socialLogin']);
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

//get number created by the user
Route::get('/user_numbers', [PhoneController::class, 'user_numbers']);
//send message
Route::post('/send', [PhoneController::class, 'send']);
//get a sigle message conversation
Route::post('/conversation', [PhoneController::class, 'conversation']);
//get details of a contact
Route::get('/contact/{phone}', [PhoneController::class, 'contact']);
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

//get audiences belonging to a workspace
Route::get('/audiences', [AudienceController::class, 'index']);

//create audience
Route::post('/create-audience', [AudienceController::class, 'create']);

//get template list
Route::get('/templates', [WorkspaceController::class, 'templates']);

//create template
Route::post('/createTemplate', [WorkspaceController::class, 'createTemplate']);

//create workspace
Route::post('/createWorkspace', [WorkspaceController::class, 'create']);

//invite members
Route::post('/invite', [WorkspaceController::class, 'invite']);

//set password
Route::post('/setPassword', [AuthController::class, 'setPassword']);

//get feedback
Route::post('/feedback', [WorkspaceController::class, 'feedback']);

//update contact info
Route::patch('/updateContact', [PhoneController::class, 'update_contact']);

//get template messages
Route::get('/templateMessages/{template_id}', [WorkspaceController::class, 'templateMessages']);

//create template message
Route::post('/createTemplateMessage', [WorkspaceController::class, 'createTemplateMessage']);

//delete template message
Route::delete('/deleteTemplateMessage/{template_message_id}', [WorkspaceController::class, 'deleteTemplateMessage']);

//modify template message
Route::patch('/modifyTemplateMessage', [WorkspaceController::class, 'modifyTemplateMessage']);