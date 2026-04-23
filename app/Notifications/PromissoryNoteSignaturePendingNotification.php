<?php

namespace App\Notifications;

use App\Models\PromissoryNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromissoryNoteSignaturePendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PromissoryNote $note,
        public string $actionUrl,
        public string $subject,
        public string $summary
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $studentName = $this->note->student?->full_name ?? trim(($this->note->student?->first_name ?? '') . ' ' . ($this->note->student?->last_name ?? ''));

        return (new MailMessage)
            ->subject($this->subject)
            ->line($this->summary)
            ->line('Student: ' . $studentName)
            ->line('PN ID: ' . $this->note->id)
            ->line('Remaining balance: ₱' . number_format((float) $this->note->remaining_balance, 2))
            ->action('Review promissory notes', $this->actionUrl);
    }
}