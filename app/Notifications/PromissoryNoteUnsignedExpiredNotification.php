<?php

namespace App\Notifications;

use App\Models\PromissoryNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromissoryNoteUnsignedExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PromissoryNote $note)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Promissory note signature expired')
            ->line('Your promissory note expired unsigned and was voided automatically.')
            ->line('PN ID: ' . $this->note->id)
            ->line('Please contact your coordinator if you need a new note issued.')
            ->action('View promissory notes', route('student.promissory_notes.index'));
    }
}