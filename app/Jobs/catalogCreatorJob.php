<?php

namespace App\Jobs;

use App\Http\Controllers\ApiController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class catalogCreatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $templateData;

    public $timeout = 120000;

    public function __construct($templateData)
    {
        $this->templateData = $templateData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $apiController = new ApiController();
        $apiController->createTemplate($this->templateData);
    }
}
