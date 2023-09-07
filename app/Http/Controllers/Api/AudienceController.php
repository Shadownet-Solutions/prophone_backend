<?php

namespace App\Http\Controllers\Api;

use Session;
use App\Models\User;
use App\Models\WorkSpace;
use App\Models\Audience;
use App\Models\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AudienceController extends Controller
{
    //protect functions
     
    public function __construct() {
        $this->middleware('auth:api');

    }


    // get audiences that belongs to a workspace
    public function index(){
        $user = Auth::user();
        if (!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'session expired'
                ], 401);
        }

        $audiences = Audience::where('workspace', $user->workspace)->withCount('contacts')->get();

        if($audiences){
            return response()->json([
                'status' => 'success',
                'data' => $audiences
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'data' => 'You have not created an audience yet'
            ], 404);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Workspace not found'
            ]);
        
    }

    public function create(Request $request){
        $user = Auth::user();
        $workspace = WorkSpace::find($user->workspace);
        //validate request
        $request->validate([
            'title' => 'required'
        ]);
        if ($workspace){
            $audience = new Audience;
            $audience->title = $request->title;
            $audience->description = $request->description;
            $audience->workspace = $workspace->id;
            $created_by = $user->id;
            $audience->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Audience created successfully, you can add contacts now',
                ]);

        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a workspace yet'
                ]);
            }
       
        
    }



}
