<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class UserRegistered extends Mailable
{
    use Queueable, SerializesModels;

    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-registered')
                ->subject('Регистрация на сайте ' . env('APP_PROTOCOL') . env('APP_DOMAIN'))
                ->with([
                    'url'      => env('APP_PROTOCOL') . $this->data->domain . '.' . env('APP_DOMAIN'),
                    'email'    => $this->data->email,
                    'password' => $this->data->originalPassword
                ]);
    }
}
