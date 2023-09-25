<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\WorkSpace;

class UserController extends Controller
{
   public function index()
   {
       return response()->json(User::all());
   }

   public function userProfile()
   {


        $user = Auth::user();
        if ($user) {
            $workspace = WorkSpace::where('id', $user->workspace)->first();
            $balance = number_format($workspace->wallet, 2);

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'balance' => $balance
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'no active session'
                ], 401);
            }
   }

   //function updateUser data

   public function updateUser(Request $request)
{
    $user = Auth::user();

    if ($request->has('first_name')) {
        $user->first_name = $request->first_name;
    }
    if ($request->has('last_name')) {
        $user->last_name = $request->last_name;
        }
    if ($request->has('company_name')) {
        $user->company_name = $request->company_name;
    }

    if ($request->has('phone')) {
        $user->phone = $request->phone;
    }

    if ($request->has('address')) {
        $user->address = $request->address;
    }


    if ($request->has('profile_image')) {
        $user->profile_image = $request->profile_image;
    }
    

    $user->save();

    return response()->json([
        'status' => 'success',
        'user' => $user
    ]);
}

//get a username and check if exist or available

public function checkUsername(Request $request)
{
    $username = $request->username;
    $user = User::where('username', $username)->first();
    if ($user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Username already exist'
            ], 409);
        }
        else{
            return response()->json([
                'status' => 'success',
                'message' => 'Username available'
                ]);
            }
        }






}
