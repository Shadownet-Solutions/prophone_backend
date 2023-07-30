<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Validator;
use App\Models\User;
use App\Models\Number;
use App\Models\Message;
use App\Models\Workspace;
use App\Models\Contact;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;


class PhoneController extends Controller
{

    //protect functions

    public function __construct() {
        $this->middleware('auth:api');
    }


//provission number
    public function provision(Request $request){
        $current_number = Number::where('created_by', Auth::id())->first();
        if($current_number){
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a number'
                ], 400);
            }
            $number = Number::create([
                'created_by' => Auth::id(),
                'number' => rand(1000000000, 9999999999),
                'label' => $request->label,
                'description' => 'Personal number',
                'workspace' => $request->workspace,
                'company_name' => $request->company_name,
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
        $numbers = Number::where('created_by', Auth::id())->get();
        return response()->json([
            'status' => 'success',
            'numbers' => $numbers
            ], 200);
        }


// get inbox messages
    public function inbox($number_id){
        $number = Number::find($number_id);
        if($number){
            $messages = Message::where('number', $number_id)->get();
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
        $number = Number::find($request->number);
        if($number){
            $message = Message::create([
                'number' => $request->number,
                'content' => $request->message,
                'sender' => Auth::id(),
                'type' => 'sent',
                'receiver' => $request->receiver,
                'workspace' => $request->workspace,
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
        $number = Number::find($request->number);
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










    } 









    
