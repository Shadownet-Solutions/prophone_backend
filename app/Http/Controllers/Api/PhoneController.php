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
use App\Models\SingleContact;
use App\Models\Campaign;
use App\Models\Template;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Telnyx\TelnyxClient;
use Telnyx\AvailablePhoneNumber;
use Telnyx\MessagingProfile;


class PhoneController extends Controller
{

    //protect functions

    public function __construct() {
        $this->middleware('auth:api');
        \Telnyx\Telnyx::setApiKey(env('TELNYX_API_KEY'));
        
    }


//provision number
    public function provision(Request $request){
        $user = Auth::user();
        
        if (!$user ){
            return response()->json([
                'status' => 'error',
                'message' => 'session not active'
                ], 401);
        }
        //check if the user has a workspace if not return error
        if(!$user->workspace){
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a workspace, Please create one or be added to one'
                ], 400);
                }
        $workspace = WorkSpace::where('id', $user->workspace)->first();

        $current_number = Number::where('workspace', $workspace->id)->first();

       // already have a number and proccess new number at $5
        if($current_number){
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a number to get additional number contact your workspace administrator'
                ], 400);
            }


            //provision the first number
            $purchaseParams = [
                'phone_number' => $request->number, 
                'messaging_profile_id' => $workspace->messaging_profile_id, 
            ];

            $purchasedNumber = AvailablePhoneNumber::purchase($purchaseParams);

            $number = Number::create([
                'created_by' => Auth::id(),
                'number' => rand(1000000000, 9999999999),
                'label' => $request->label,
                'type' => 'Toll Free',
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

     // get numbers createdm and associated to a user
     public function user_numbers(){
        $user = Auth::user();
        //check if user is logged
        if (!$user ){
            return response()->json([
                'status' => 'error',
                'message' => 'session not active'
                ], 401);
            }
        $numbers = Number::where('type', 'Toll Free')->get();
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
            // get one message per phoneNumber and order by latest

            $latestMessages = Message::select('messages.*')
            ->where('number', $number_id)
            ->joinSub(
                Message::select('phoneNumber', DB::raw('MAX(created_at) as max_created_at'))
                    ->groupBy('phoneNumber'),
                'latest',
                function ($join) {
                    $join->on('messages.phoneNumber', '=', 'latest.phoneNumber')
                        ->on('messages.created_at', '=', 'latest.max_created_at');
                }
            )
            ->orderBy('messages.created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'messages' => $latestMessages
        ], 200);


            }




            // $messages = $message->getInbox($number_id);
            // // $messages = Message::where('number', $number_id)->get();
            // return response()->json([
            //     'status' => 'success',
            //     'messages' => $messages
            //     ], 200);
            

        return response()->json([
            'status' => 'error',
            'message' => 'Number not found'
            ], 400);
        }

//send message to a single number
    public function send(Request $request){
        $user = Auth::user();
        if (!$user ){
            return response()->json([
                'status' => 'error',
                'message' => 'session not active'
                ], 401);
                }
        
        $number = Number::find($request->number);

        if($number){
            $message = Message::create([
                'number' => $request->number,
                'phoneNumber' => $request->to,
                'content' => $request->message,
                'from' => $user->id,
                'type' => 'outgoing',
                'to' => $request->to,
                // 'workspace' => $user->workspace,
                'status' => 'completed',
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
        
        if($number){
            $messages = Message::where('number', $request->number)->where('phoneNumber', $request->phoneNumber)->get();
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

        public function contact($phone){
            $contact = SingleContact::where('phone', $phone)->first();
           

            if(!$contact){
                $newContact = SingleContact::create([
                    'phone' => $phone,
                    'userId' => Auth::user()->id,
                    ]);
                    
                    return response()->json([
                        'status' => 'success',
                        'contact' => $newContact
                        ], 200);
                    } else {

                    $notes = Note::where('contact', $phone)->get();
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

    //update singlecontact information
    public function update_contact(Request $request){
        $contact = SingleContact::find($request->contact);
        if($contact){
                $contact->update($request->all());
                return response()->json([
                    'status' => 'success',
                    'contact' => $contact
                    ], 200);
                    }
        return response()->json([
            'status' => 'error',
            'message' => 'contact not found'
            ], 400);
            }



    // get dashboard data 
    public function analytics(){
        $user = Auth::user();
        $messages = Message::where('from', $user->id)->orWhere('to', $user->id)->get()->count();
        $unique =  Message::where('from', $user->id)->where('status', 'completed')->orWhere('to', $user->id)->get()->count();
        $sent = Message::where('from', $user->id)->where('status', 'completed')->orWhere('to', $user->id)->get()->count();
        $received = Message::where('to', $user->id)->where('status', 'completed')->orWhere('to', $user->id)->get()->count();
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
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Note added'
                    ], 200);
                }


    //number search
public function search_number(Request $request){
    $user = Auth::user();
   

    try {
    $searchParams = [
        'filter[country_code]' => 'US',
        'filter[national_destination_code]' => $request->national_destination_code,
        // 'filter[phone_number][starts_with]' => '359',
        'filter[locality]' => $request->city,
        // 'filter[administrative_area]' => 'IL',
        'filter[number_type]' => $request->type,
        


        'filter[limit]' => 10,
    ];

    $numbers = AvailablePhoneNumber::all($searchParams);

    return response()->json([
        'status' => 'success',
        'numbers' => $numbers
        ], 200);
    } catch (\Exception $e) {
        //handle exceptions
        return response()->json([
            'status' => 'error',
            'message' => 'No number found for the given query, Please adjust and try again'
            ], 500);
        }


}









    } 









    
