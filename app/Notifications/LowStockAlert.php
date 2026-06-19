<?php

namespace App\Notifications;

use App\Models\Medicine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(public readonly Medicine $medicine) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert — ' . $this->medicine->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A medicine has dropped to or below its minimum stock level.')
            ->line('**Medicine:** ' . $this->medicine->name)
            ->line('**Generic Name:** ' . $this->medicine->generic_name)
            ->line('**Current Stock:** ' . $this->medicine->stock_quantity . ' ' . $this->medicine->unit)
            ->line('**Minimum Stock:** ' . $this->medicine->minimum_stock . ' ' . $this->medicine->unit)
            ->action('Manage Stock', url('/medicines/' . $this->medicine->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'low_stock_alert',
            'title'         => 'Low Stock Alert',
            'message'       => $this->medicine->name . ' is low on stock (' . $this->medicine->stock_quantity . ' remaining)',
            'medicine_id'   => $this->medicine->id,
            'medicine_name' => $this->medicine->name,
            'current_stock' => $this->medicine->stock_quantity,
            'min_stock'     => $this->medicine->minimum_stock,
            'url'           => url('/medicines/' . $this->medicine->id),
        ];
    }
}
