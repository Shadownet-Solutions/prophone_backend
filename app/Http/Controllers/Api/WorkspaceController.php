<?php

namespace App\Http\Controllers\Api;

use Session;
use App\Models\User;
use App\Models\WorkSpace;
use App\Models\Feedback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Audience;
use Illuminate\Support\Facades\Auth;
use App\Models\Template;
use App\Models\TemplateMessage;
use Mail;
use Str;
use App\Models\Invitation;
use App\Mail\InviteExistingUser;
use App\Mail\InviteNewUser;
use Telnyx\MessagingProfile;

class WorkspaceController extends Controller
{
    
        //protect functions
     
        public function __construct() {
         $this->middleware('auth:api',[
            'except' => [
                'webhook'
                ]
            
        ]);
         \Telnyx\Telnyx::setApiKey(env('TELNYX_API_KEY'));
     }
     
     //get team members
      public function getTeamMembers() {
         $user = Auth::user();
        $workspace = WorkSpace::find($user->workspace);
        if ($workspace) {
         $team = User::where('workspace', $user->workspace)->get();
            return response()->json([
                'status' => 'success',
                'team' => $team
            ], 200);

         } else {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a workspace'
                ], 404);
         }
         
     
     
     
     }


     // create workspace
     public function create(Request $request) {
        
         $user = Auth::user();
         //check if user already has a workspace
         if ($user->workspace) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'You already have a workspace'
                 ], 400);
                 }
            $name = $request->title;

                
            // $profile = MessagingProfile::Retrieve("40018aca-3953-4ca1-9496-b93ae627b38b");
            // $profile->delete();

            // return $profile;

            try{

                $createProfile = MessagingProfile::Create(
                    [
                        
                        "name" => $name,
                        
                        "webhook_url" => "https://app.prophone.io/api/webhook"
                    ]);

                // return $createProfile;


       

                $workspace = new WorkSpace;
                $workspace->title = $name;
                $workspace->messaging_profile_id = $createProfile->id;
                $workspace->wallet = 0;
                $workspace->description = $request->description;
                $workspace->image = $request->image;
                $workspace->status = 'active';
                $workspace->wallet = 0;
                $workspace->created_by = $user->id;
                $workspace->save();

                // add the workspace to the user
                $user->workspace = $workspace->id;
                $user->save();

                //return response
                return response()->json([
                    'status' => 'success',
                    'message' => 'Workspace created successfully'
                    ], 200);



        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
                ], 400);
        
            }

      }


    // invite members to workspace
    public function invite(Request $request) {
        $user = Auth::user();
        $workspace = Workspace::find($user->workspace);
        
        if (!$workspace) {
            return response()->json([
                'status' => 'error',
                'message' => 'Workspace not found, Please create one first'
                ], 404);
                }

        // Check if the email is already registered
        $member = User::where('email', $request->email)->first();
        


        try {
            if ($member) {
                // if sender is trying to invite himself
                if ($member->id == $user->id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You cannot invite yourself'
                        ], 400);
                        }
                // If the member is registered, update the workspace ID
                // check that member doesn't already belong to a workspace
                if ($member->workspace) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User already belongs to a workspace'
                        ], 400);
                    }
                $member->workspace = $workspace->id;
                $member->save();
    
                // Send an email to the member
                Mail::to($member->email)->send(new InviteExistingUser($workspace));
            } else {
                // If the email is not registered, send an invitation email
                $token = Str::random(60);
                $invitation = Invitation::create([
                    'email' => $request->email,
                    'token' => $token,
                    'workspace' => $workspace->id,
                    'invited_by' => $user->id,
                    'status' => 'pending',
                    'role' => $request->role
                ]);
    
                Mail::to($request->email)->send(new InviteNewUser($invitation, $workspace));
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Invitation sent successfully'
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception, such as logging the error
            \Log::error('Failed to send invitation email: ' . $e->getMessage());
    
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }



    }


    //sumbit feedback
    public function feedback(Request $request) {
        $user = Auth::user();
        $workspace = Workspace::find($user->workspace);
        $feedback = new Feedback;
        $feedback->user_id = $user->id;
        $feedback->message = $request->feedback;
        $feedback->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Feedback submitted successfully'
            ], 200);
        }

    //get workspace wallet balance

    public function balance(){
        $user = Auth::user();
        $workspace = WorkSpace::find($user->workspace);
        if($workspace){
            return response()->json([
                'status' => 'success',
                'balance' => number_format($workspace->wallet, 2)
                ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No workspace found'
                ], 400);
            }
     }
    
     
     
    //get templates using workspace id
    public function templates(){
        $user = Auth::user();
        if(!$user->workspace){
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a workspace, Please create one be added to one'
                ], 400);
                }


        $templates = Template::where('workspace', $user->workspace)->get();
        if($templates->isNotEmpty()){
            return response()->json([
                'status' => 'success',
                'templates' => $templates
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No templates found for the workspace'
                    ], 400);
            }

        }

        // create template
    public function createTemplate(Request $request){
        $user = Auth::user();
        //check if user has a workspace
        if(!$user->workspace){
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have a workspace, Please create one be added to one'
                ], 400);
                }

        $template = Template::create([
            'title' => $request->title,
            'created_by' => $user->id,
            'workspace' => $user->workspace,
            ]);

            //get messages array and create for each message
            $messages = $request->messages;
            $backup = TemplateMessage::create([
                'template_id' => $template->id,
                    'body' => $request->backupText,
                    'type' => 'backup',
                    'created_by' => $user->id,
            ]);
            foreach($messages as $message){
                TemplateMessage::create([
                    'template_id' => $template->id,
                    'body' => $message,
                    'type' => 'message',
                    'created_by' => $user->id,
                    ]);
                    }

            return response()->json([
                'status' => 'success',
                'message' => 'Template created Successfuly',
                'template' => $template
                ], 200);
            }


        //get templates messages
        public function templateMessages($template_id){
            $user = Auth::user();
            $messages = TemplateMessage::where('template_id', $template_id)->get();

            if($messages){
                return response()->json([
                    'status' => 'success',
                    'messages' => $messages
                    ], 200);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'No messages found for the template'
                            ], 400);
                    
            
                        }
                    }


    //create template message

        public function createTemplateMessage(Request $request){
            $user = Auth::user();
            $template = Template::find($request->template_id);
            if(!$template){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template not found'
                    ], 400);
                }
            //check if message is up to 10 already
                $count = TemplateMessage::where('template_id', $template->id)->count();
                if($count >= 10){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Maximum of 10 messages allowed'
                        ], 400);
                        }
                //create the message
                $message = TemplateMessage::create([
                    'template_id' => $request->template_id,
                    'body' => $request->message,
                    'created_by' => $user->id,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Message created',
                        'message' => $message
                        ], 200);
                    }


            //delete template message
            public function deleteTemplateMessage($id){
                $message = TemplateMessage::find($id);
                if(!$message){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Message not found'
                        ], 400);
                    }
                // delete the message
                $message->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message deleted'
                    ], 200);
                }

            //modify template message
            public function modifyTemplateMessage(Request $request){
                $message = TemplateMessage::find($request->id);
                if(!$message){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Message not found'
                        ], 400);
                    }
                    $message->body = $request->message;
                    $message->save();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Message modified'
                        ], 200);
                    }
        
     
}
