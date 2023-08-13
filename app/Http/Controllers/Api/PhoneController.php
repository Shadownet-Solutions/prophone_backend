<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Validator;
use App\Models\User;
use App\Models\Number;
use App\Models\Message;
use App\Models\WorkSpace;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\Template;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PhoneController extends Controller
{

    //protect functions

    public function __construct() {
        $this->middleware('auth:api');
    }


//provision number
    public function provision(Request $request){
        $user = Auth::user();
        //check if the user has a workspace if not return error
        
        if(!$user->workspace){
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a workspace, Please create one be added to one'
                ], 400);
                }
        $workspace = WorkSpace::where('id', $user->workspace)->first();

        $current_number = Number::where('workspace', $workspace->id)->first();

        // dd($workspace);
        if($current_number){
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a number to get additional number contact your workspace administrator'
                ], 400);
            }
            $number = Number::create([
                'created_by' => Auth::id(),
                'number' => rand(1000000000, 9999999999),
                'label' => $request->label,
                'description' => 'Personal number',
                'workspace' => $workspace->id,
                // 'company_name' => $request->company_name,
                'status' => 'Active',

                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Number Provisioned',
                    'number' =>  $number->number
                    ], 200);
                

    }

//get numbers associated to a workspace
    public function numbers(){

        $user = Auth::user();
        //check if the user has a workspace if not return error
        
        if(!$user->workspace){
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a workspace, Please create one be added to one'
                ], 400);
                }
        $workspace = WorkSpace::where('id', $user->workspace)->first();
        

        $numbers = Number::where('workspace', $workspace->id)->get();
        return response()->json([
            'status' => 'success',
            'numbers' => $numbers
            ], 200);
        }


// get inbox messages
    public function inbox($number_id){
        $user = Auth::user();
        $number = Number::find($number_id);
        if($number){

            $latestMessages = Message::select('messages.*')
            ->where('number', $number_id)
            ->joinSub(
                Message::select('receiver', DB::raw('MAX(created_at) as max_created_at'))
                    ->groupBy('receiver'),
                'latest',
                function ($join) {
                    $join->on('messages.receiver', '=', 'latest.receiver')
                        ->On('messages.created_at', '=', 'latest.max_created_at');
                }
            )
            ->orderBy('messages.created_at', 'desc')
            ->take(10)
            ->get();
            
            return response()->json([
                'status' => 'successs',
                'messages' => $latestMessages
                ], 200);






            $messages = $message->getInbox($number_id);
            // $messages = Message::where('number', $number_id)->get();
            return response()->json([
                'status' => 'success',
                'messages' => $messages
                ], 200);
            }

        return response()->json([
            'status' => 'error',
            'message' => 'Number not found'
            ], 400);
        }

//send message to a single number
    public function send(Request $request){
        $user = Auth::user();
        $workspace = WorkSpace::where('id', $user->workspace)->first();
        $number = Number::find($request->number);
        if($number){
            $message = Message::create([
                'number' => $request->number,
                'content' => $request->message,
                'sender' => $user->id,
                'type' => 'sent',
                'receiver' => $request->receiver,
                'workspace' => $workspace->id,
                'status' => 'Sent',
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message Sent'
                    ], 200);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sender Number not found'
                    ], 400);
    }


//get a single conversation

    public function conversation(Request $request){
        $user = Auth::user();
        $number = Number::find($request->number);
        $workspace = WorkSpace::where('id', $user->workspace)->first();
        if($number){
            $messages = Message::where('number', $request->number)->where('receiver', $request->receiver)->orWhere('sender', $request->receiver)->get();
            return response()->json([
                'status' => 'success',
                'messages' => $messages
                ], 200);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'message not found using the number provided'
                ], 400);
            }

//get a single contact details and notes associated

        public function contact($id){
            $contact = Contact::find($id);
            if($contact){
                $notes = Note::where('contact', $id)->get();
                return response()->json([
                    'status' => 'success',
                    'contact' => $contact,
                    'notes' => $notes
                    ], 200);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'contact not found'
                    ], 400);
                }

    // get dashboard data come back and modify campaigns
    public function analytics(){
        $messages = Message::where('sender', Auth::id())->get()->count();
        $unique =  Message::where('sender', Auth::id())->where('status', 'Sent')->get()->count();
        $sent = Message::where('sender', Auth::id())->where('status', 'Sent')->get()->count();
        $received = Message::where('sender', Auth::id())->where('status', 'Received')->get()->count();
        $campaings = Campaign::where('status', 'in-progress')->get();



        return response()->json([
            'status' => 'success',
            'messages' => $messages,
            'unique' => $unique,
            'sent' => $sent,
            'received' => $received,
            'campaigns' => $campaings
            ], 200);
    }

    // get contacts belonging to a workspace
    public function contacts($workspace){
         //check if the user has a workspace if not return error
         $user = Auth::user();
         $workspace = WorkSpace::where('id', $user->workspace)->first();
         if(!$workspace){
             return response()->json([
                 'status' => 'error',
                 'message' => 'You do not have a workspace, Please create one be added to one'
                 ], 400);
                 }
        
        $contacts = Contact::where('workspace', $workspace)->get();
        if($contacts){  
        return response()->json([
            'status' => 'success',
            'contacts' => $contacts
            ], 200);
        }
        return response()->json([   
            'status' => 'error',
            'message' => 'No contacts found for the workspace'
            ], 400);
        }

        // add note
        public function add_note(Request $request){
            $note = Note::create([
                'note' => $request->note,
                'contact' => $request->contact,
                'created_by' => Auth::id(),
                'workspace' => $request->workspace
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Note added'
                    ], 200);
                }


    









    } 









    
