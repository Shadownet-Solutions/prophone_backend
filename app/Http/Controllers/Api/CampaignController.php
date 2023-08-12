<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\Number;
use App\Models\Message;
use App\Models\WorkSpace;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
class CampaignController extends Controller
{
    //protect functions

    public function __construct() {
        $this->middleware('auth:api');
    }



    //get all campaigns
    public function index($workspace) {
        $campaigns = Campaign::where('workspace', $workspace)->get();
        if($campaigns) {
        return response()->json([
            'status' => 'success',
            'data' => $campaigns
            ]);
        } else {
            return response()->json([
                    'status' => 'error',
                    'message' => 'No campaigns found'
                    ]);
                }
        }
        
    
}
