<?php

namespace Modules\Analysis\Notifications;

use Modules\Analysis\Models\Analysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnalysisFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Analysis $analysis) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $analysis = $this->analysis;

        return (new MailMessage)
            ->subject('QC alert: failed or low-purity analysis #'.$analysis->id)
            ->line('A QC analysis needs attention.')
            ->line('Sample: '.(string) $analysis->sample_name)
            ->line('Batch: '.(string) $analysis->batch_no)
            ->line('Result: '.(string) ($analysis->result ?: '—'))
            ->line('Purity: '.($analysis->purity === null ? '—' : number_format((float) $analysis->purity, 2).'%'))
            ->action('Open analysis sheet', route('analysis.index'));
    }
}
