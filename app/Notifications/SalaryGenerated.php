<?php

namespace App\Notifications;

use App\Models\SalaryPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryGenerated extends Notification
{
    use Queueable;

    public function __construct(public readonly SalaryPayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $monthName = date('F', mktime(0, 0, 0, $this->payment->month, 1));

        return (new MailMessage)
            ->subject("Salary Slip Generated — {$monthName} {$this->payment->year}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Your salary slip for {$monthName} {$this->payment->year} has been generated.")
            ->line('**Net Salary:** ₨ ' . number_format($this->payment->net_salary, 2))
            ->line('**Status:** ' . ucfirst($this->payment->status))
            ->action('View Salary Slip', url('/salaries/' . $this->payment->id . '/slip'));
    }

    public function toArray(object $notifiable): array
    {
        $monthName = date('F', mktime(0, 0, 0, $this->payment->month, 1));

        return [
            'type'       => 'salary_generated',
            'title'      => 'Salary Slip Generated',
            'message'    => "Your salary slip for {$monthName} {$this->payment->year} is ready (₨ " . number_format($this->payment->net_salary, 2) . ')',
            'payment_id' => $this->payment->id,
            'month'      => $this->payment->month,
            'year'       => $this->payment->year,
            'net_salary' => $this->payment->net_salary,
            'status'     => $this->payment->status,
            'url'        => url('/salaries/' . $this->payment->id . '/slip'),
        ];
    }
}
