<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFcmNotification implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected int $userId;
    protected string $title;
    protected string $description;

    public function __construct(int $userId, string $title, string $description)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
    }

    public function handle()
    {
        (new FcmService())->sendFcmNotification($this->userId, $this->title, $this->description);
    }
}
