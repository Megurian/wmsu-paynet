<?php

namespace App\Notifications;

use App\Models\PromissoryNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromissoryNoteSignatureApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Promissory note approved')
            ->line('Your promissory note has been reviewed and approved by the coordinator.')
            ->line('PN ID: ' . $this->note->id)
            ->line('The note is now active and will be used for settlement and clearance checks.')
            ->action('View promissory notes', route('student.promissory_notes.index'));
    }
}