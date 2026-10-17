<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SchoolInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $schoolName,
        public string $invitedEmail,
        public string $invitationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invitation à rejoindre '.$this->schoolName.' sur YELENSCOL');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.school-invitation',
            with: [
                'schoolName' => $this->schoolName,
                'invitedEmail' => $this->invitedEmail,
                'invitationUrl' => $this->invitationUrl,
            ],
        );
    }
}
