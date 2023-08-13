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
use Mail;
use Str;
use App\Models\Invitation;
use App\Mail\InviteExistingUser;
use App\Mail\InviteNewUser;


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
             'status' => 'success',
             'team' => $team
         ], 200);
     
     
     
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
         $workspace = new WorkSpace;
         $workspace->title = $request->title;
         $workspace->description = $request->description;
         $workspace->image = $request->image;
         $workspace->status = 'active';
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

     
     
     
    //get templates using workspace id
    public function templates($workspace){
        $templates = Template::where('workspace', $workspace)->get();
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
        $workspace = Workspace::find($user->workspace);
        $template = Template::create([
            'title' => $request->title,
            'content' => $request->content,
            'created_by' => Auth::id(),
            'workspace' => $workspace->id,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Template created'
                ], 200);
            }
     
}
