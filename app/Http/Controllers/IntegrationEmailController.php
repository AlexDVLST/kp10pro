<?php

namespace App\Http\Controllers;

use App\Jobs\ClientNotOpenLetterJob;
use App\Jobs\ClientOpenLetterJob;
use App\Jobs\ScenarioJob;
use App\Models\OfferHistory;
use App\Models\Scenario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IntegrationEmail;
use Illuminate\Support\Facades\Log;
use App\Models\Offer;
use App\Http\Traits\OfferTrait;
use App\Scopes\OfferScope;
use Intervention\Image\Facades\Image;

class IntegrationEmailController extends Controller
{
    use OfferTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($account, Request $request)
    {
        $request->validate([
            'login'    => 'required',
            'password' => 'required',
            'server'   => 'required',
            'port'     => 'required',
            'userId'   => 'required',
        ]);

        $user = Auth::user();

        //Check user settings
        if (true !== ($checkSmtp = $this->checkSmtp($request))) {
            return $checkSmtp;
        }

        try {
            $result = IntegrationEmail::create([
                'smtp_login'    => $request->get('login'),
                'smtp_password' => $request->get('password'),
                'smtp_server'   => $request->get('server'),
                'smtp_port'     => $request->get('port'),
                'smtp_secure'   => $request->get('secure'),
                'user_id'       => $request->get('userId'),
                'account_id'    => $user->accountId
            ]);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($account, $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($account, Request $request, $id)
    {
        $request->validate([
            'login'    => 'required',
            'password' => 'required',
            'server'   => 'required',
            'port'     => 'required',
            'userId'   => 'required',
        ]);

        $user = Auth::user();

        //Check user settings
        if (true !== ($checkSmtp = $this->checkSmtp($request))) {
            return $checkSmtp;
        }

        $integrationEmail = IntegrationEmail::whereId($id)->whereUserId($request->get('userId'))->first();

        if (!$integrationEmail) {
            return response()->json(['errors' => __('messages.integration.email.not_found')], 422);
        }

        try {
            $integrationEmail->update([
                'smtp_login'    => $request->get('login'),
                'smtp_password' => $request->get('password'),
                'smtp_server'   => $request->get('server'),
                'smtp_port'     => $request->get('port'),
                'smtp_secure'   => $request->get('secure'),
                'user_id'       => $request->get('userId'),
                'account_id'    => $user->accountId
            ]);

            return response()->json($integrationEmail);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($account, $id, Request $request)
    {
        $integrationEmail = IntegrationEmail::whereId($id)->whereUserId($request->get('userId'))->first();

        if (!$integrationEmail) {
            return response()->json(['errors' => __('messages.integration.smtp.not_found')], 422);
        }

        try {
            $integrationEmail->delete();

            return response()->json($integrationEmail);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    public function json()
    {
        //Get email settings
        return response()->json([]);
    }

    /**
     * Check stmp settings
     *
     * @param Request $requset
     * @return json
     */
    public function checkSmtp(Request $request)
    {
        $login    = $request->get('login');
        $password = $request->get('password');
        $server   = $request->get('server');
        $port     = $request->get('port');
        $secure   = $request->get('secure');

        try {
            $transport = (new \Swift_SmtpTransport($server, $port, $secure ? 'ssl' : ''))
                ->setUsername($login)
                ->setPassword($password)
                ->setTimeout(1);

            $mailer = new \Swift_Mailer($transport);
            $mailer->getTransport()->start();
        } catch (\Swift_TransportException $e) {
            Log::error($e->getMessage());
            return response()->json(['errors' => __('messages.integration.smtp.auth.error')], 422);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return true;
    }

    public function send($account, Request $request)
    {
        $request->validate([
            'offerId'     => 'required',
            'subject'     => 'required',
            'clientEmail' => 'required',
            'message'     => 'required',
        ]);

        $offer = Offer::whereId($request->get('offerId'))->first();

        //Offer not found
        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        //Find smtp settings
        $integrationEmail = IntegrationEmail::with('user')->whereId($request->get('smtpId'))->first();

        if (!$integrationEmail) {
            return response()->json(['errors' => __('messages.integration.smtp.not_found')], 422);
        }

        $excel   = $request->get('excel');
        $pdf     = $request->get('pdf');
        $pdfFull = $request->get('pdfFull');
        $message = $request->get('message');

        //For change status to viewed
        $message .= "<img src='".env('APP_PROTOCOL').'admin.'.env('APP_DOMAIN')."/integration/email/offer/{$offer->id}/state'>";

        try {
            $transport = (new \Swift_SmtpTransport($integrationEmail->smtp_server, $integrationEmail->smtp_port, $integrationEmail->smtp_secure ? 'ssl' : ''))
                ->setUsername($integrationEmail->smtp_login)
                ->setPassword($integrationEmail->smtp_password)
                ->setTimeout(10);

            $mailer = new \Swift_Mailer($transport);

            $message = (new \Swift_Message())
                ->setSubject($request->get('subject'))
                ->setFrom([$integrationEmail->smtp_login => $integrationEmail->user->surname . ' ' . $integrationEmail->user->name])
                ->setTo([$request->get('clientEmail')])
                ->setBody($message, 'text/html');

            //Attach excel
            if ($excel) {
                // $message->attach(\Swift_Attachment::fromPath('/Users/mk462/Projects/kp10-ru/storage/exports/КП_№________________от_12-10-18.xls'));
            }

            // Send the message
            $result = $mailer->send($message);

            //Change offer state
            $this->setOfferState($offer->id, 2);

            $user = Auth::user();

            //Для сценария (Клиент не открыл письмо в течении)
            ScenarioJob::dispatch(2, 2, $user, $offer);

            //Для сценария (Менеджер отправил письмо с КП)
            ScenarioJob::dispatch(7, 2, $user, $offer);

        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    /**
     * Change offer state after open from email
     *
     * @param int $id
     * @param Request $request
     * @return void
     */
    public function setEmailOfferState($id, Request $request)
    {
        $offer = Offer::with('clientRelation')->withoutGlobalScope(OfferScope::class)->whereId($id)->first();

        if($offer){
            //Change offer state
            $this->setOfferState($offer->id, 3);

            //Для сценария (Клиент открыл письмо)
            ScenarioJob::dispatch(1, 1, '', $offer, $offer->clientRelation->client_id);
        }

        // create a new empty image resource
        $img = Image::canvas(1, 1, '#FFFFFF');

        // send HTTP header and output image data
        return $img->response();
    }
}
