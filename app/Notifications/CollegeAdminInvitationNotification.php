<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CollegeAdminInvitationNotification extends ResetPassword
{
    protected string $roleLabel;
    protected string $collegeName;

    public function __construct(string $token, string $roleLabel, string $collegeName)
    {
        parent::__construct($token);

        $this->roleLabel = $roleLabel;
        $this->collegeName = $collegeName;
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(__('You are invited to join :appName', ['appName' => config('app.name')]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->first_name ?? $notifiable->name ?? '']))
            ->line(__('You have been invited to become the :role for :college.', [
                'role' => $this->roleLabel,
                'college' => $this->collegeName,
            ]))
            ->line(__('To activate your account and choose a password, click the button below.'))
            ->action(__('Set Your Password'), $url)
            ->line(__('This link will expire in :count minutes.', ['count' => config('auth.passwords.users.expire')]))
            ->line(__('If you did not expect this invitation, you may ignore this email.'));
    }
}
