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
    public function index($workspace){
        $audiences = Audience::where('workspace', $workspace)->withCount('contacts')->get();

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

}
