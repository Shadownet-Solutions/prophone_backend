<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteExistingUser extends Mailable
{
    use Queueable, SerializesModels;

    public $workspace;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($workspace)
    {
        $this->workspace = $workspace;
       
    }


    public function build()
    {
        

        return $this->subject('You have been added to a workspace')
                        ->view('emails.existing_user_invitation')
                        ->with('workspaceName', $this->workspace->title);
                    
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    // public function envelope()
    // {
    //     return new Envelope(
    //         subject: 'Invite Existing User',
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
    // public function attachments()
    // {
    //     return [];
    // }
}
