<?php

namespace App\Http\Controllers;

use Session;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Audience;
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
    public function index($workspace){
        $audiences = Audience::where('workspace_id',$workspace)->get();

        if($audiences){
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

}
