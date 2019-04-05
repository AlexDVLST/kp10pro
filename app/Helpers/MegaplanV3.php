<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\IntegrationMegaplan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use function GuzzleHttp\json_decode;
use Exception;

class MegaplanV3
{
    public $url;
    public $accessToken;
    public $errors  = [];
    public $headers = [];

    public function __construct($params = [])
    {
        $accessToken = false;
        $url         = false;

        // if (!empty($params) && is_array($params)) {
        if (isset($params['accessToken']) && isset($params['url'])) {
            $accessToken = $params['accessToken'];
            $url         = $params['url'];
        }

        if (isset($params['account_id'])) {
            $account = IntegrationMegaplan::withoutGlobalScope(IntegrationMegaplanScope::class)
                        ->whereAccountId( $params['account_id'] )->orderBy('updated_at', 'desc')->first();//->account_id

            $accessToken = $account->api_token;
            $url         = 'https://'.$account->host;
        }

        if (empty($params)) {
            $integration = IntegrationMegaplan::first();

            $accessToken = $integration->api_token;
            $url         = 'https://'.$integration->host;
        }

        //replace accessToken with custom
        if ($accessToken) {
            $this->accessToken = $accessToken;
        }
        //replace crm url with custom
        if ($url) {
            $this->url = $url;
        }

        // IntegrationMegaplan
    }

    /**
     * @return array|mixed|object
     */
    public function getAccountInfo()
    {
        return $this->requestGet('accountInfo');
    }

    /**
     * get userSign info
     *
     * @param $param
     * @return bool|mixed
     */
    public function getUserSign($param)
    {
        if (!is_array($param)) {
            Log::debug('getUserSign: no options for GET request!');
            return false;
        }
        return $this->requestGet("integration/{$param['uuid']}/checkUserSign/{$param['userSign']}");
    }

    /**
     * get Deal Program list
     *
     * @param array $param
     * @return mixed
     */
    public function getDealProgram($param = [])
    {
        return $this->requestGet('program', $param);
    }

    /**
     * get deal card
     *
     * @param $id
     * @return mixed
     */
    public function getDeal($id)
    {
        return $this->requestGet('deal/' . $id);
    }

    /**
     * Обновление сделки
     *
     * @param $id
     * @param $param
     * @return mixed
     */
    public function updateDeal($id, $param)
    {
        return $this->requestPost('deal/' . $id, $param);
    }

    /**
     * Обновление статуса сделки
     *
     * @param $id
     * @param $param
     * @return mixed
     */
    public function updateDealStatus($id, $param)
    {
        $param['contentType'] = 'DealAction';

        return $this->requestPost('deal/' . $id . '/apply', $param);
    }

    /**
     * Создание дела
     *
     * @param $param
     * @return mixed
     */
    public function createTodo($param)
    {
        $param['contentType'] = 'Todo';

        return $this->requestPost('todo', $param);
    }

    /**
     * Создание нового товара
     *
     * @param $param
     * @return mixed
     */
    public function createNewProduct($param)
    {
        $param['contentType'] = 'Offer';

        $result = $this->requestPost('offer', $param);

        if($result->meta->status == 200){
            return $result->data;
        }

        return $result->meta->errors;
    }

    /**
     * get info about client type human
     *
     * @param $id
     * @return mixed
     */
    public function getContractorHuman($id)
    {
        return $this->requestGet('contractorHuman/' . $id);
    }

    /**
     * get info about client type company
     *
     * @param $id
     * @return mixed
     */
    public function getContractorCompany($id)
    {
        return $this->requestGet('contractorCompany/' . $id);
    }

    /**
     * get program field list
     *
     * @param $id
     * @return mixed
     */
    public function getProgramField($id)
    {
        return $this->requestGet("program/$id/fields");
    }

    /**
     * @param $param
     * @return array|mixed|object
     */
    public function getUserSetting($param)
    {
        return $this->requestGet('userSetting/' . $param);
    }

    /**
     * get users filter list
     *
     * @param $param
     * @return mixed
     */
    public function getUsersFilterList($param)
    {
        return $this->requestGet('crmFilter/' . $param);
    }

    /**
     * @param $param
     * @return array|mixed|object
     */
    public function getUsersList($param)
    {
        return $this->requestGet('contractor/' , $param);
    }

        /**
     * @param $id
     *
     * @return array|mixed|object
     */
    public function getEmployee($id = '', $return = 0)
    {
        $result = $this->requestGet('employee/', $id, $return);

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param $param
     * @return mixed
     */
    public function setUserSetting($param)
    {
        $param['contentType'] = 'UserSetting';

        return $this->requestPost('userSetting', $param);
    }

    /**
     * @param $req_headers
     */
    public function setHeaders($req_headers)
    {
        if (is_array($req_headers) && !empty($req_headers)) {
            $this->headers = $req_headers;
        }
    }

    /**
     * Set authorization for user
     *
     * @param $user_id
     */
    public function setUser($user_id)
    {
        $this->setHeaders(['X-User-Id' => $user_id]);
    }

    /**
     * @param $uri
     * @param string $param
     * @param int $return
     * @return mixed
     * @throws Exception
     */
    private function requestGet($uri, $param = '', $return = 0)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept'        => 'application/json',
        ];

        if (is_array($param)) {
            $param   = !empty($param) ? '?' . json_encode($param, JSON_UNESCAPED_UNICODE) : '';
        }

        $client = new Client(['base_uri' => $this->url . '/api/v3/']);

        try {
            $res = $client->request('GET', $uri . $param, [
                'headers' => $headers
            ]);
        } catch (ClientException $e) {
            $response             = $e->getResponse();

            $responseBodyAsString = json_decode($response->getBody()->getContents());
            // $megaplanError        = $responseBodyAsString['meta']['errors'][0]['message'];
            Log::debug( print_r($responseBodyAsString, 1) );
            // return $responseBodyAsString;
            throw new Exception($responseBodyAsString->meta->errors[0]->message);
        }

        if ($return == 1) {
            return json_decode($res->getBody()->getContents());
        }

        return json_decode($res->getBody()->getContents())->data;
    }

    /**
     * @param $uri
     * @param $param
     * @return mixed
     * @throws Exception
     */
    private function requestPost($uri, $param)
    {
        $this->headers['Authorization'] =  'Bearer ' . $this->accessToken;
        $this->headers['Accept']        = 'application/json';

        $vars = json_encode($param, JSON_UNESCAPED_UNICODE);
        $client = new Client([
            'base_uri' => $this->url . '/api/v3/',
        ]);

        try {
            $response = $client->request('POST', $uri, [
                'body'    => $vars,
                'headers' => $this->headers
            ]);
            $body = $response->getBody()->getContents();
        } catch (ClientException $e) {
            $response             = $e->getResponse();

            $responseBodyAsString = json_decode($response->getBody()->getContents());
            // $megaplanError        = $responseBodyAsString['meta']['errors'][0]['message'];
            Log::debug( print_r($responseBodyAsString, 1) );
            // return $responseBodyAsString;
            throw new Exception($responseBodyAsString->meta->errors[0]->message);
        }

        return json_decode($body);
    }

    /**
     * @param $function
     * @param $params
     * @param array $result
     * @return array
     */
    public function getRecursively($function, $params, &$result = [])
    {
        //curl multi !!
        $contentType = '';

        //Get contentType for request
        if ($function == 'getDeal') {
            $contentType = 'Deal';
        }
        if ($function == 'getTask') {
            $contentType = 'Task';
        }
        if ($function == 'getHistory') {
            $contentType = 'Comment';
        }
        if ($function == 'getEmployee') {
            $contentType = 'employee';
        }

        //If data exists
        if (! empty($result)) {
            $params['pageAfter']['contentType'] = $contentType;
            $params['pageAfter']['id']          = end($result)['id'];
        }

        //Call user function
        $data = call_user_func( ['App\Helpers\MegaplanV3', $function], $params, 1);

        if ($data && $data->meta->status == 200) {
            $result = array_merge($result, $data->data);

            //Get more data
            if ($data->meta->pagination->hasMoreNext) {
                $this->getRecursively($function, $params, $result);
            }
        } else {
            $this->_log([$params, $data->meta->errors]);
        }

        return $result;
    }
}
