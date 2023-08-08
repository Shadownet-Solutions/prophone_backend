<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteNewUser extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $workspace;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invitation, $workspace)
    {
        $this->invitation = $invitation;
        $this->workspace = $workspace;
    }

    public function build()
    {
        
        $url = "https://app.ptophone.io/sign-up/invite?token={$this->invitation->token}";


        return $this->subject('You have been added to a workspace')
                        ->view('emails.new_user_invitation')
                        ->with([
                            'workspaceName' => $this->workspace->title,
                            'url' => $url
                        ]);
                    
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    // public function envelope()
    // {
    //     return new Envelope(
    //         subject: 'Invite New User',
    //     );
    // }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    // public function content()
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
