<?php

namespace App\Notifications;

use App\Models\LabBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LabResultReady extends Notification
{
    use Queueable;

    public function __construct(public readonly LabBooking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Lab Results Ready — ' . $this->booking->booking_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Lab results are ready for the following booking.')
            ->line('**Patient:** ' . $this->booking->patient?->name)
            ->line('**Booking #:** ' . $this->booking->booking_number)
            ->line('**Date:** ' . $this->booking->booking_date?->format('d M Y'))
            ->action('View Results', url('/lab/' . $this->booking->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'lab_result_ready',
            'title'          => 'Lab Results Ready',
            'message'        => 'Results ready for ' . $this->booking->patient?->name . ' (' . $this->booking->booking_number . ')',
            'booking_id'     => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'patient_name'   => $this->booking->patient?->name,
            'url'            => url('/lab/' . $this->booking->id),
        ];
    }
}
