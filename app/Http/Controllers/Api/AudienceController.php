<?php

namespace App\Http\Controllers\Api;

use Session;
use App\Models\User;
use App\Models\Workspace;
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

        $audiences = Audience::where('workspace', $user->workspace)->withCount('contacts')->get();

        if($audiences->isNotEmpty()){
            return response()->json([
                'status' => 'success',
                'data' => $audiences
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'data' => 'No audiences found'
            ], 404);
        }
        
    }

    public function create(Request $request){
        $user = Auth::user();
        $workspace = Workspace::find($user->workspace);
        $request->validate([
            'title' => 'required'
        ]);
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
        
    }



}
