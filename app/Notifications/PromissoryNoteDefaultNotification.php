<?php

namespace App\Notifications;

use App\Models\PromissoryNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromissoryNoteDefaultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PromissoryNote $note, public string $stage = PromissoryNote::STATUS_DEFAULT)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->stage === PromissoryNote::STATUS_BAD_DEBT
            ? 'Promissory note marked as bad debt'
            : 'Promissory note marked as defaulted';

        $message = $this->stage === PromissoryNote::STATUS_BAD_DEBT
            ? 'Your promissory note has been escalated to bad debt after the academic year ended.'
            : 'Your promissory note is now defaulted because the semester has ended and the balance remains unpaid.';

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->line('PN ID: ' . $this->note->id)
            ->line('Remaining balance: ₱' . number_format((float) $this->note->remaining_balance, 2))
            ->action('View promissory notes', route('student.promissory_notes.index'));
    }
}