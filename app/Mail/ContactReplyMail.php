<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $originalMessage,
        public ?string $replyMessage,
        public Carbon $repliedAt
    ) {}

    public function build()
    {
        return $this->subject('Phản hồi từ Handicraft Shop')
                    ->replyTo(config('mail.from.address'), config('mail.from.name'))
                    ->view('pages.contact_reply');
    }
}