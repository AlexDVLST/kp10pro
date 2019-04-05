<?php

namespace App\Helpers;

use App\Models\Page;
use App\Models\IntegrationAmocrm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Scopes\IntegrationAmocrmScope;
use Exception;

class Amocrm
{
    public $url;
    public $headers = [
        'Accept' => 'application/json'
    ];
    private $client = '';
    private $errors = [
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable'
    ];
    private $auth = false;

    public function __construct($params = '')
    {
        $user = Auth::user();
        if (!$params) {
            $params = $user->account_id;
        }
        if (!is_array($params)) {
            $params = ['account_id' => $params];
        }

        $login = '';
        $token = '';
        $host  = '';

        if (isset($params['account_id'])) {
            //Get settings by account
            $integration = IntegrationAmocrm::withoutGlobalScope(IntegrationAmocrmScope::class)->whereAccountId($params['account_id'])->first();
            if ($integration) {
                $login = $integration->login;
                $token = $integration->api_token;
                $host = $integration->host;
            }
        }
        if (isset($params['login']) && isset($params['host']) && isset($params['token'])) {
            $login = $params['login'];
            $token = $params['token'];
            $host = $params['host'];
        }
        if ($login && $token && $host) {
            $this->client = new Client([
                'cookies' => true
            ]);

            $this->url = 'https://' . $host;

            $user = [
                'USER_LOGIN' => $login,
                'USER_HASH'  => $token
            ];

            //Authorization
            $response = $this->requestPost('/private/api/auth.php?type=json', $user);

            if ($response && isset($response->auth)) {
                $this->auth = $response->auth;
            }
        } else {
            throw new Exception('Login, host and token required');
        }
    }

    /**
     * Get account info
     *
     * @param array $param
     * @return object
     */
    public function account($param = [])
    {
        return $this->requestGet('/api/v2/account', $param);
    }

    /**
     * Get custom fields
     *
     * @return object
     */
    public function customFields()
    {
        $response = $this->account(['with' => 'custom_fields']);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->custom_fields)) {
            return $response->_embedded->custom_fields;
        }
        return $response;
    }

    /**
     * Get users
     *
     * @return object
     */
    public function users()
    {
        $response = $this->account(['with' => 'users', 'free_users' => 'Y']);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->users)) {
            return $response->_embedded->users;
        }
        return $response;
    }

    /**
     * Get pipelines
     *
     * @return object
     */
    public function pipelines()
    {
        $response = $this->account(['with' => 'pipelines']);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->items)) {
            return $response->_embedded->items;
        }

        return $response;
    }

    /**
     * Get groups
     *
     * @return object
     */
    public function groups()
    {
        $response = $this->account(['with' => 'groups']);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->groups)) {
            return $response->_embedded->groups;
        }
        return $response;
    }

    /**
     * Get note_types
     *
     * @return object
     */
    public function noteTypes()
    {
        $response = $this->account(['with' => 'note_types']);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->note_types)) {
            return $response->_embedded->note_types;
        }
        return $response;
    }

    /**
     * Get task_types
     *
     * @return object
     */
    public function taskTypes()
    {
        $response = $this->account(['with' => 'task_types']);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->task_types)) {
            return $response->_embedded->task_types;
        }
        return $response;
    }

    /**
     * Get lead list
     *
     * @return object
     */
    public function leads($param = [])
    {
        $response = $this->requestGet('/api/v2/leads', $param);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->items)) {
            return $response->_embedded->items;
        }
        return $response;
    }

    /**
     * Get lead card
     *
     * @return object
     */
    public function lead($id)
    {
        $response = $this->leads(['id' => $id]);
        //Check if object exist
        if (is_array($response)) {
            return $response[0];
        }
        return $response;
    }

    /**
     * Get company card
     *
     * @param array $param
     * @return object
     */
    public function companies($param = [])
    {
        $response = $this->requestGet('/api/v2/companies', $param);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->items)) {
            return $response->_embedded->items[0];
        }
        return $response;
    }

    /**
     * Get company card
     *
     * @param int $id
     * @return object
     */
    public function company($id)
    {
        $response = $this->companies(['id' => $id]);
        //Check if object exist
        if (is_array($response)) {
            return $response[0];
        }
        return $response;
    }

    /**
     * Get contacts
     *
     * @param array $param
     * @return object
     */
    public function contacts($param = [])
    {
        $response = $this->requestGet('/api/v2/contacts', $param);
        //Check if object exist
        if (isset($response->_embedded) && isset($response->_embedded->items)) {
            return $response->_embedded->items;
        }
        return $response;
    }

    /**
     * Get catalogs
     *
     * @param array $param
     * @return object
     */
    public function catalogs(array $param = [])
    {
        return $this->requestGet('/api/v2/catalogs', $param);
    }

    /**
     * Create catalogs
     *
     * @param array $param
     * @return object
     */
    public function addCatalogs(array $param)
    {
        return $this->requestPost('/api/v2/catalogs', $param);
    }

    /**
     * Get catalog element
     *
     * @param array $param
     * @return object
     */
    public function catalogElements(array $param = [])
    {
        return $this->requestGet('/api/v2/catalog_elements', $param);
    }

    /**
     * Get catalog element
     *
     * @param array $param
     * @return object
     */
    public function addCatalogElements(array $param)
    {
        return $this->requestPost('/api/v2/catalog_elements', $param);
    }

    /**
     * Get webhooks
     *
     * @param array $param
     * @return object
     */
    public function webhooks(array $param = [])
    {
        return $this->requestGet('/api/v2/webhooks', $param);
    }

    /**
     * Set webhook
     *
     * @param array $param
     * @return object
     */
    public function addWebhook(array $param)
    {
        return $this->requestPost('/api/v2/webhooks/subscribe', $param);
    }

    /**
     * Remove webhook
     *
     * @param array $param
     * @return object
     */
    public function removeWebhook(array $param)
    {
        return $this->requestPost('/api/v2/webhooks/unsubscribe', $param);
    }

    /**
     * Создать задачу
     *
     * @param array $param
     * @return object
     */
    public function createTask(array $param)
    {
        return $this->requestPost('/api/v2/tasks', $param);
    }

    /**
     * Получить список воронок и этапов продаж
     *
     * @param array $param
     * @return object
     */
    public function getPipelines($param = [])
    {
        $response = $this->requestGet('/api/v2/pipelines', $param);

        if (isset($response->_embedded) && isset($response->_embedded->items)) {
            return $response->_embedded->items;
        }

        return $response;
    }

    /**
     * Обновить сделку
     *
     * @param array $param
     * @return mixed
     */
    public function updateLead(array $param)
    {
        return $this->requestPost('/api/v2/leads', $param);
    }

    /**
     * Request GET
     *
     * @param $uri
     * @param array $param
     * @return mixed
     * @throws Exception
     */
    private function requestGet($uri, $param = [])
    {
        $body = [];

        //If isset id and this is array
        if (!empty($param) && isset($param['id']) && is_array($param['id'])) {
            $param['id'] = implode(',', $param['id']);
        }

        try {
            $response = $this->client->request('GET', $this->url . $uri, [
                'headers' => $this->headers,
                'query'   => $param
            ]);

            $body = $response->getBody()->getContents();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();

            throw new Exception(json_decode($body)->response->error);
        }

        return json_decode($body);
    }

    /**
     * Request POST
     *
     * @param $uri
     * @param $param
     * @return mixed
     * @throws Exception
     */
    private function requestPost($uri, $param)
    {
        $body = [];
        $vars = json_encode($param, JSON_UNESCAPED_UNICODE);

        try {
            $response = $this->client->request('POST', $this->url . $uri, [
                'body'    => $vars,
                'headers' => $this->headers
            ]);

            $body = $response->getBody()->getContents();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody()->getContents());
            $error = '';

            if (isset($body->response)) {
                $error = $body->response->error;
            }
            if (isset($body->detail)) {
                $error = $body->detail;
            }

            throw new Exception($error);
        }

        return json_decode($body);
    }
}
