<?php

namespace App\Notifications;

use App\Models\PromissoryNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromissoryNoteReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PromissoryNote $note, public int $daysBeforeDue = 7)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Promissory note payment reminder')
            ->line('Your promissory note payment is approaching its due date.')
            ->line('PN ID: ' . $this->note->id)
            ->line('Due date: ' . optional($this->note->due_date)->format('M d, Y'))
            ->line('Remaining balance: ₱' . number_format((float) $this->note->remaining_balance, 2))
            ->action('View promissory notes', route('student.promissory_notes.index'));
    }
}