<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;

class PolytellEmailParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $source;
    private $referer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, int $source = 0, $referer = '')
    {
        $this->user   = $user;
        $this->source = $source;
        $this->referer = $referer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;

        if ($user) {
            
            // Create deal in megaplan POLYTELL
            $HttpClient = new HttpClient();

            $field1 = $user->surname . ' ' . $user->name;
            $field2 = $user->email;
            $field3 = $user->phone;

            //Get source name
            switch ($this->source) {
                case 1: //Megaplan
                    $sourceName = 'Megaplan';
                    break;
                case 2: //AmoCRM
                    $sourceName = 'AmoCRM';
                    break;
                case 3: //Bitrix24
                    $sourceName = 'Bitrix24';
                    break;
                default:
                    $sourceName = $this->referer;
                    break;
            }

            $param = [
                'field1'      => $field1, //'Имя',
                'field2'      => $field2, //'Почта',
                'field3'      => $field3, //Телефоны', // поля клиента
                'dealField1'  => $user->domain . '<br>' . $field1 . '<br>' . $field2 . '<br>' . $field3, //'Суть',
                'dealField2'  => $field2, //'Идентификатор',
                'dealField3'  => $sourceName, //'Источник', // поля сделки
                'template_id' => '134',
                'token'       => 'MGlkYUc1VWQ4U0Z0c0pRU1hhSk9Kb3JlUGNJNXpsNnM4N0IvdWY4a2lIblozbDRnK1Z2L0xZMmVxb0pLdEg5Ng==',
                'model'       => 'emailparserjson'
            ];

            $vars = json_encode($param, JSON_UNESCAPED_UNICODE);

            try {
                $response = $HttpClient->request('POST', 'https://i.polytell.ru/', [
                    'body'    => $vars
                ]);

                $body = json_decode($response->getBody()->getContents());

                if (isset($body->status) && $body->status != 'ok') {
                    Log::error('Error EMAILPARSER response: ' . print_r($body,1) . ' for user ' . $user->email . ' ' . $user->domain);
                } elseif (!isset($body->status)) {
                    Log::error('Error EMAILPARSER response: ' . print_r($body,1) . ' for user ' . $user->email . ' ' . $user->domain);
                }
            } catch (ClientException $e) {
                $response = $e->getResponse();
                $body     = json_decode($response->getBody()->getContents());
                Log::error($body);
            }
        }
    }
}
