<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;

class ConfirmRegistration extends Mailable
{
    use Queueable, SerializesModels;


    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data_arr = [
            'login' => $this->user->login,
            'confirm_link' => url('register/'.$this->user->confirm_token),
            'siteLink' => url('/')
        ];

        return $this
            ->from('info@nanidub.com', 'nanidub')
            ->subject('Подтвердите свой email')
            ->view('mails.confirmMail', $data_arr);
    }
}
