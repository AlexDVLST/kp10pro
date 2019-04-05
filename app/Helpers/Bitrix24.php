<?php

namespace App\Helpers;

use App\Models\IntegrationBitrix24;
use App\Models\Page;
use App\Models\IntegrationAmocrm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Scopes\IntegrationBitrix24Scope;
use Exception;

class Bitrix24
{
    /**
     * @var string OAuth server
     */
    const OAUTH_SERVER = 'oauth.bitrix.info';


    /**
     * @var string access token
     */
    protected $accessToken;

    /**
     * @var string refresh token
     */
    protected $refreshToken;

    /**
     * @var string domain
     */
    protected $domain;

    /**
     * @var array scope
     */
    protected $applicationScope = array();

    /**
     * @var string application id
     */
    protected $applicationId = 'app.5baa327b059b90.52213217';

    /**
     * @var string application secret
     */
    protected $applicationSecret = 'kMkJCQTiadh9B1WLjulcroH9bmnfOFxK3sXyS925gF3UF58UAa';

    /**
     * @var array raw request, contain all cURL options array and API query
     */
    protected $rawRequest;

    /**
     * @var array, contain all api-method parameters, vill be available after call method
     */
    protected $methodParameters;

    /**
     * @var array request info data structure акщь curl_getinfo function
     */
    protected $requestInfo;

    /**
     * @var bool if true raw response from bitrix24 will be available from method getRawResponse, this is debug mode
     */
    protected $isSaveRawResponse = false;

    /**
     * @var array raw response from bitrix24
     */
    protected $rawResponse;

    /**
     * @var string redirect URI from application settings
     */
    protected $redirectUri;

    /**
     * @var string portal GUID
     */
    protected $memberId;

    /**
     * @var array custom options for cURL
     */
    protected $customCurlOptions;

    /**
     * @see https://github.com/Seldaek/monolog
     * @var \Monolog\Logger PSR-3 compatible logger, use only from wrappers methods log*
     */
    protected $log;

    /**
     * @var integer CURL request count retries
     */
    protected $retriesToConnectCount;

    /**
     * @var integer retries to connect timeout in microseconds
     */
    protected $retriesToConnectTimeout;

    /**
     * @var array pending batch calls
     */
    protected $_batch = array();

    /**
     * @var callable callback for expired tokens
     */
    protected $_onExpiredToken;

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

    /**
     * Create a object to work with Bitrix24 REST API service
     *
     * Bitrix24 constructor.
     */
    public function __construct($params = '')
    {
        $user = Auth::user();
        if (!$params) {
            $params = $user->account_id;
        }
        if (!is_array($params)) {
            $params = ['account_id' => $params];
        }

        if (isset($params['account_id'])) {
            //Get settings by account
            $integration = IntegrationBitrix24::withoutGlobalScope(IntegrationBitrix24Scope::class)->whereAccountId($params['account_id'])->first();
            if ($integration) {
                self::setDomain($integration->host);
                self::setAccessToken($integration->access_token);
                self::setRefreshToken($integration->refresh_token);

                $this->isSaveRawResponse = false;

                $this->setRetriesToConnectCount(1);
                $this->setRetriesToConnectTimeout(1000000);

                $this->client = new Client([
                    'cookies' => true
                ]);
            } else {
                throw new Exception('Host and others parameters required');
            }
        } else {
            throw new Exception('Host and others parameters required');
        }
    }

    /**
     * Get custom fields
     *
     * @return array
     */
    public function customFields()
    {
        $dealFields = self::requestGet('crm.deal.fields', '', '');

        $customFields = [];
        if (!empty($dealFields['result'])) {
            foreach ($dealFields['result'] as $item => $field) {
                $getCF = explode('UF_CRM_', $item);

                if (!empty($getCF[1])) {

                    /**
                     * не пропускаем типы полей:
                     *  - мультиселектовые списки
                     *  - адрес
                     *  -
                     */
                    if (($field['type'] == 'enumeration' && $field['isMultiple']) || $field['type'] == 'address' || $field['type'] == 'resourcebooking' || $field['type'] == 'file') {
                        continue;
                    }

                    $customFields[] = [
                        'field_id'   => $item,
                        'field_name' => $field['listLabel'],
                        'field_type' => $field['type'],
                        'items'      => !empty($field['items']) ? $field['items'] : ''
                    ];
                }
            }
        }

        return $customFields;
    }

    /**
     * Получаем карточку сделки
     *
     * @param $id
     * @return array
     */
    public function getDealCard($id)
    {
        $deal = self::requestGet('crm.deal.get', ['id' => $id], '');

        if (isset($deal['result']['ID'])) {
            return $deal['result'];
        }

        return [];
    }

    /**
     * Получаем сотрудников компании
     *
     * @param $start
     * @param $count
     * @param $employees
     * @return array
     */
    public function getUsers(&$employees = [], $start = 0, $count = 1)
    {
        $response = self::requestGet('user.get', ['start' => $start], '');
        $employees = array_merge($response['result'], $employees);

        if (isset($response['next']) && isset($response['total']) && $response['total'] > $response['next'] * $count) {
            self::getUsers($employees, $response['next'], $count++);
        }

        return $employees;
    }

    /**
     * Получаем информацию по компании
     *
     * @param $id
     * @return mixed
     */
    public function getCompanyInfo($id)
    {
        $companyInfo = self::requestGet('crm.company.get', ['id' => $id], '');

        if (isset($companyInfo['result']['ID'])) {
            return $companyInfo['result'];
        }

        return [];
    }

    /**
     * Получение информации о контакте
     *
     * @param $id
     * @return mixed
     */
    public function getContactInfo($id)
    {
        $contactInfo = self::requestGet('crm.contact.get', ['id' => $id], '');

        if (isset($contactInfo['result']['ID'])) {
            return $contactInfo['result'];
        }

        return [];
    }

    /**
     * Получение списка контактов, привязанных к сделке
     *
     * @param $id
     * @return array|mixed
     */
    public function getContactsFromDeal($id)
    {
        $contacts = self::requestGet('crm.deal.contact.items.get', ['id' => $id], '');

        if (!empty($contacts['result'])) {
            return $contacts['result'];
        }

        return [];
    }

    /**
     * Обновить сделку на Bitrix24
     *
     * @param $model
     * @return bool
     */
    public function updateB24Deal($model)
    {
        $updateDeal = self::requestGet('crm.deal.update', $model, '');

        if (!empty($updateDeal['result'])) {
            return true;
        }

        return false;
    }

    /**
     * Создать новую встречу по сделке
     *
     * @param $model
     * @return array
     */
    public function addNewTaskInDeal($model)
    {
        $createMeeting = self::requestGet('crm.activity.add', $model, '');

        if (!empty($createMeeting['result'])) {
            return $createMeeting['result'];
        }

        return [];
    }

    /**
     * Получить список типов коммуникаций
     *
     * @return mixed
     */
    public function getCommunicationInfo()
    {
        return self::requestGet('crm.activity.communication.fields', '', '');
    }

    /**
     * Получить список статусов сделки
     * $id = направление сделки (воронка)
     *
     * @return array
     */
    public function getListDealStatus($params = [])
    {
        $statuses = self::requestGet('crm.dealcategory.stage.list', $params, '');

        if (!empty($statuses['result'])) {
            return $statuses['result'];
        }

        return [];
    }

    /**
     * Получить список воронок
     *
     * @param array $params
     * @return array
     */
    public function getPipelines($params = [])
    {
        $statuses = self::requestGet('crm.dealcategory.list', $params, '');

        if (!empty($statuses['result'])) {
            return $statuses['result'];
        }

        return [];
    }

    /**
     * Создание нового товара
     *
     * @param $product
     * @return string
     */
    public function createNewProduct($product)
    {
        $create = self::requestGet('crm.product.add', $product, '');

        if (!empty($create['result'])) {
            return $create['result'];
        }

        return '';
    }

    /**
     * Обновление списка товаров в сделке
     *
     * @param $model
     * @return bool
     */
    public function updateProductsListInDeal($model)
    {
        $updateList = self::requestGet('crm.deal.productrows.set', $model, '');

        if (isset($updateList['result']) && $updateList['result']) {
            return true;
        }

        return false;
    }

    /**
     * Получить список товаров по сделке
     *
     * @param $model
     * @return bool
     */
    public function getProductsListInDeal($model)
    {
        $updateList = self::requestGet('crm.deal.productrows.set', $model, '');

        if (isset($updateList['result']) && $updateList['result']) {
            return true;
        }

        return false;
    }

    /**
     * Обновление товара
     *
     * @param $product
     * @return bool
     */
    public function updateProduct($product)
    {
        $update = self::requestGet('crm.product.update', $product, '');

        if (isset($update['result']) && $update['result']) {
            return true;
        }

        return false;
    }

    /**
     * Получить список валют
     *
     * @return array
     */
    public function getCurrencies()
    {
        $currencies = self::requestGet('crm.currency.list', '', '');

        if (!empty($currencies['result'])) {
            return $currencies['result'];
        }

        return [];
    }

    /**
     * Получить типы сущностей (клиент, компания, сделка, лид, дело ...)
     *
     * @return mixed
     */
    public function getType()
    {
        return self::requestGet('crm.enum.ownertype', '', '');
    }

//    public function info()
//    {
//        return self::requestGet('crm.activity.communication.fields', '', '');
//    }

    public function getInfoTypes()
    {
        return self::requestGet('crm.enum.activitytype', '', '');
    }

    /**
     * @param int $retriesCnt
     * @return bool
     */
    public function setRetriesToConnectCount($retriesCnt = 1)
    {
        $this->retriesToConnectCount = (int)$retriesCnt;
        return true;
    }

    /**
     * @param int $microseconds
     * @return bool
     */
    public function setRetriesToConnectTimeout($microseconds = 1000000)
    {
        $this->retriesToConnectTimeout = $microseconds;
        return true;
    }

    /**
     * Get domain
     *
     * @return string | null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param $domain
     * @return bool
     * @throws Exception
     */
    public function setDomain($domain)
    {
        if ('' === $domain) {
            throw new Exception('domain is empty');
        }
        $this->domain = $domain;
        return true;
    }

    /**
     * Get access token
     *
     * @return string | null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param $accessToken
     * @return bool
     * @throws Exception
     */
    public function setAccessToken($accessToken)
    {
        if ('' === $accessToken) {
            throw new Exception('access token is empty');
        }
        $this->accessToken = $accessToken;
        return true;
    }

    /**
     * Get refresh token
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param $refreshToken
     * @return bool
     * @throws Exception
     */
    public function setRefreshToken($refreshToken)
    {
        if ('' === $refreshToken) {
            throw new Exception('refresh token is empty');
        }
        $this->refreshToken = $refreshToken;
        return true;
    }

    /**
     * Get memeber ID
     *
     * @return string | null
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * @param $memberId
     * @return bool
     * @throws Exception
     */
    public function setMemberId($memberId)
    {
        if ('' === $memberId) {
            throw new Exception('memberId is empty');
        } elseif (null === $memberId) {
            throw new Exception('memberId is null');
        }
        $this->memberId = $memberId;
        return true;
    }

    /**
     * Get application secret
     *
     * @return string
     */
    public function getApplicationSecret()
    {
        return $this->applicationSecret;
    }

    /**
     * @param $applicationSecret
     * @return bool
     * @throws Exception
     */
    public function setApplicationSecret($applicationSecret)
    {
        if ('' === $applicationSecret) {
            throw new Exception('application secret is empty');
        }
        $this->applicationSecret = $applicationSecret;
        return true;
    }

    /**
     * Get application id
     *
     * @return string
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * @param $applicationId
     * @return bool
     * @throws Exception
     */
    public function setApplicationId($applicationId)
    {
        if ('' === $applicationId) {
            throw new Exception('application id is empty');
        }
        $this->applicationId = $applicationId;
        return true;
    }

    /**
     * get retries to connect timeout in microseconds
     *
     * @return mixed
     */
    public function getRetriesToConnectTimeout()
    {
        return $this->retriesToConnectTimeout;
    }

    /**
     * Post Request
     *
     * @param $methodName
     * @param $param
     * @return mixed
     * @throws Exception
     */
    private function requestPost($methodName, $param)
    {
        //проверяем access_token
        $accessToken = self::isAccessTokenExpired();

        //вышел срок жизни токена и новый токен получить не удалось
        if (!$accessToken) {
            return ['status' => false, 'error' => 'Some problems with your tokens (access, refresh)'];
        }

        $url = 'https://' . $this->domain . '/rest/' . $methodName;
        $param['auth'] = $this->accessToken;

        $body = [];
        $vars = json_encode($param, JSON_UNESCAPED_UNICODE);

        try {
            $response = $this->client->request('POST', $url, [
                'body'    => $vars,
                'headers' => $this->headers
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
     * Get Request
     *
     * @param $methodName
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    private function requestGet($methodName, $params = [], $url = '')
    {
        //проверяем access_token
        $accessToken = self::isAccessTokenExpired();

        //вышел срок жизни токена и новый токен получить не удалось
        if (!$accessToken) {

            return ['status' => false, 'error' => 'Some problems with your tokens (access, refresh)'];
        }

        $query = $params;
        if (!$url) {
            $url = 'https://' . $this->domain . '/rest/' . $methodName;

            $query = ['auth' => $this->accessToken];
            if ($params) {
                $query = array_merge($query, $params);
            }
        }

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => $this->headers,
                'query'   => $query
            ]);

            $body = $response->getBody()->getContents();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = $response->getBody()->getContents();

            throw new Exception($body);
        }

        return json_decode($body, true);
    }

    /**
     * Handling bitrix24 api-level errors
     *
     * @param $arRequestResult
     * @param $methodName
     * @param array $additionalParameters
     * @return null
     * @throws Exception
     */
    protected function handleBitrix24APILevelErrors($arRequestResult, $methodName, array $additionalParameters = array())
    {
        if (array_key_exists('error', $arRequestResult)) {
            $errorMsg = sprintf('%s - %s in call [%s] for domain [%s]',
                $arRequestResult['error'],
                (array_key_exists('error_description', $arRequestResult) ? $arRequestResult['error_description'] : ''),
                $methodName,
                $this->getDomain());

            // throw specific API-level exceptions
            switch (strtoupper(trim($arRequestResult['error']))) {
                case 'WRONG_CLIENT':
                case 'ERROR_OAUTH':
                    throw new Exception($errorMsg);
                case 'ERROR_METHOD_NOT_FOUND':
                    throw new Exception($errorMsg);
                case 'INVALID_TOKEN':
                case 'INVALID_GRANT':
                    throw new Exception($errorMsg);
                case 'EXPIRED_TOKEN':
                    throw new Exception($errorMsg);
                case 'PAYMENT_REQUIRED':
                    throw new Exception($errorMsg);
                case 'NO_AUTH_FOUND':
                    throw new Exception($errorMsg);
                default:
                    throw new Exception($errorMsg);
            }
        }
        return null;
    }

    /**
     * Вышел ли срок жизни токена ?
     * да - обновляем доступ
     * нет - оставляем прежний
     *
     * @return bool
     * @throws Exception
     */
    public function isAccessTokenExpired()
    {
        $url = 'https://' . $this->domain . '/rest/app.info?auth=' . $this->accessToken;

        try {

            $this->client->request('GET', $url, [
                'headers' => $this->headers
            ]);

            return true;

        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            $body = $response->getBody()->getContents();

            //токен "умер"
            if (strpos($body, 'expired_token') !== false) {

                /**
                 * Обновляем токен
                 */
                return $this->getNewTokens();
            }

            return false;
        }
    }

    /**
     * @return bool
     */
    public function getNewTokens()
    {
        /**
         * Обновляем токен
         */
        $url = 'https://' . $this->domain . '/oauth/token/?client_id=' . $this->applicationId .
            '&grant_type=refresh_token&client_secret=' . $this->applicationSecret .
            '&refresh_token=' . $this->refreshToken;

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => $this->headers
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (!empty($body['access_token'])) {

                $this->setAccessToken($body['access_token']);
                $this->setRefreshToken($body['refresh_token']);

                IntegrationBitrix24::whereHost($this->domain)->update([
                    'access_token'  => $body['access_token'],
                    'refresh_token' => $body['refresh_token']
                ]);

                return true;
            }

            return false;
        } catch (ClientException $e) {

            return false;
        }
    }
}
