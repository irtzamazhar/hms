<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentScheduled extends Notification
{
    use Queueable;

    public function __construct(public readonly Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment Scheduled — ' . $this->appointment->appointment_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new appointment has been scheduled.')
            ->line('**Patient:** ' . $this->appointment->patient?->name)
            ->line('**Doctor:** Dr. ' . $this->appointment->doctor?->user?->name)
            ->line('**Date & Time:** ' . $this->appointment->appointment_datetime?->format('d M Y, h:i A'))
            ->line('**Type:** ' . ucfirst(str_replace('_', ' ', $this->appointment->type)))
            ->action('View Appointment', url('/appointments/' . $this->appointment->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'                 => 'appointment_scheduled',
            'title'                => 'Appointment Scheduled',
            'message'              => 'Appointment ' . $this->appointment->appointment_number . ' scheduled for ' . $this->appointment->patient?->name,
            'appointment_id'       => $this->appointment->id,
            'appointment_number'   => $this->appointment->appointment_number,
            'patient_name'         => $this->appointment->patient?->name,
            'doctor_name'          => $this->appointment->doctor?->user?->name,
            'appointment_datetime' => $this->appointment->appointment_datetime?->toDateTimeString(),
            'url'                  => url('/appointments/' . $this->appointment->id),
        ];
    }
}
