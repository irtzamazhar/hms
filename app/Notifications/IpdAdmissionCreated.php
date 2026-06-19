<?php

namespace App\Notifications;

use App\Models\IpdAdmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IpdAdmissionCreated extends Notification
{
    use Queueable;

    public function __construct(public readonly IpdAdmission $admission) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Patient Admitted — ' . $this->admission->admission_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new IPD admission has been recorded.')
            ->line('**Patient:** ' . $this->admission->patient?->name)
            ->line('**Ward:** ' . $this->admission->ward?->name . ' | **Bed:** ' . $this->admission->bed?->bed_number)
            ->line('**Doctor:** Dr. ' . $this->admission->doctor?->user?->name)
            ->line('**Admission Time:** ' . $this->admission->admission_datetime?->format('d M Y, h:i A'))
            ->action('View Admission', url('/ipd/' . $this->admission->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'ipd_admission_created',
            'title'            => 'Patient Admitted',
            'message'          => $this->admission->patient?->name . ' admitted — ' . $this->admission->admission_number,
            'admission_id'     => $this->admission->id,
            'admission_number' => $this->admission->admission_number,
            'patient_name'     => $this->admission->patient?->name,
            'ward'             => $this->admission->ward?->name,
            'bed'              => $this->admission->bed?->bed_number,
            'url'              => url('/ipd/' . $this->admission->id),
        ];
    }
}
