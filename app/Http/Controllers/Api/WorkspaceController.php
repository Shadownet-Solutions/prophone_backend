<?php

namespace App\Http\Controllers\Api;

use Session;
use App\Models\User;
use App\Models\Workspace;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class WorkspaceController extends Controller
{
    
        //protect functions
     
        public function __construct() {
         $this->middleware('auth:api');
     }
     
     //get team members
      public function getTeamMembers() {
         $user = Auth::user();
         $team = User::where('workspace', $user->workspace)->get();
         return response()->json([
             'message' => 'success',
             'team' => $team
         ], 200);
     
     
     
     }
     
     
     
     
     
}
