<?php

namespace App\Jobs;

use App\Helpers\Bitrix24;
use App\Models\Integration;
use App\Models\User;
use App\Scopes\IntegrationScope;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;

class ExtendAuthorizationB24Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance
     *
     * ExtendAuthorizationB24Job constructor.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $integrationB24 = Integration::withoutGlobalScope(IntegrationScope::class)->whereSystemCrmId(3)->get();

        if($integrationB24){
            foreach ($integrationB24 as $b24Account){
                $B24 = new Bitrix24($b24Account['account_id']);
                $B24->getNewTokens();
            }
        }
    }
}
