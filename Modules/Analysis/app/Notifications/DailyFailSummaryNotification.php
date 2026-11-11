<?php

namespace Modules\Analysis\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyFailSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<array{id:int,sample_name:?string,batch_no:?string,purity:mixed,result:?string}>  $rows
     */
    public function __construct(
        public readonly string $date,
        public readonly array $rows,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Daily QC fail summary · '.$this->date)
            ->line('Failed / low-purity analyses for '.$this->date.': '.count($this->rows));

        foreach (array_slice($this->rows, 0, 20) as $row) {
            $mail->line(sprintf(
                '#%d %s / %s · %s · %s',
                $row['id'],
                $row['sample_name'] ?? '—',
                $row['batch_no'] ?? '—',
                $row['result'] ?? '—',
                $row['purity'] === null ? '—' : number_format((float) $row['purity'], 2).'%'
            ));
        }

        return $mail->action('Open dashboard', route('dashboard'));
    }
}
