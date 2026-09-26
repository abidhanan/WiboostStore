<?php

namespace App\Jobs;

use App\Mail\UserBroadcastMail;
use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendUserBroadcastEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 45;

    public function __construct(
        public int $campaignId,
        public string $recipientEmail,
        public string $recipientName,
        public string $subjectText,
        public string $bodyText,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
    ) {
    }

    public function handle(): void
    {
        $campaign = EmailCampaign::query()->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        if ($campaign->status === 'queued') {
            $campaign->update(['status' => 'processing']);
        }

        Mail::to($this->recipientEmail, $this->recipientName)->send(new UserBroadcastMail(
            recipientName: $this->recipientName,
            subjectText: $this->subjectText,
            bodyText: $this->bodyText,
            actionLabel: $this->actionLabel,
            actionUrl: $this->actionUrl,
        ));

        EmailCampaign::query()->whereKey($this->campaignId)->increment('sent_count');
        $this->finishCampaignWhenComplete();
    }

    public function failed(Throwable $exception): void
    {
        if (! EmailCampaign::query()->whereKey($this->campaignId)->exists()) {
            return;
        }

        EmailCampaign::query()->whereKey($this->campaignId)->increment('failed_count');
        $this->finishCampaignWhenComplete();
    }

    private function finishCampaignWhenComplete(): void
    {
        $campaign = EmailCampaign::query()->find($this->campaignId);

        if (! $campaign || ($campaign->sent_count + $campaign->failed_count) < $campaign->recipient_count) {
            return;
        }

        $campaign->update([
            'status' => $campaign->sent_count === 0
                ? 'failed'
                : ($campaign->failed_count > 0 ? 'completed_with_errors' : 'completed'),
            'completed_at' => now(),
        ]);
    }
}
