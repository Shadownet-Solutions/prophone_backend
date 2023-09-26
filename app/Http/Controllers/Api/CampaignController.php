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
        if($campaigns->isNotEmpty()) {
        return response()->json([
            'status' => 'success',
            'data' => $campaigns
            ]);
        } else {
            return response()->json([
                    'status' => 'error',
                    'message' => 'No Campaigns Yet'
                    ]);
                }
        }


    //create campaign
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'number' => 'required',
            'template' => 'required',
            'audience' => 'required'
            ]);
            if($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                    ]);
                }
            //create campaign
            $campaign = new Campaign;
            $campaign->title = $request->name;
            $campaign->number = $request->number;
            $campaign->template = $request->template;
            $campaign->audience = $request->audience;
            $campaign->status = 'submitted',
            $campaign->rate_control = $request->rate_control;
            //schedule is an array

            // $campaign->schedule = $request->schedule;
            $campaign->start_time = $request->start_time;
            $campaign->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Campaign Created Successfully'
                ]);


     }
        
    
}
