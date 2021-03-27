<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;

class SendPasswordResetEmail implements ShouldQueue
{
    ///SendPasswordResetEmail
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $name;
    private $token;
    private $email;
    private $projectURL;
    private $expirationTimeInHours;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $token, $email, $projectURL, $expirationTimeInHours)
    {
        $this->name = $name;
        $this->token = $token;
        $this->email = $email;
        $this->projectURL = $projectURL;
        $this->expirationTimeInHours = $expirationTimeInHours;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $mailData = array('name'=>$this->name, 'token'=>$this->token, 'projectURL'=>$this->projectURL, 'expirationTimeInHours'=>$this->expirationTimeInHours);
        Mail::send(['html'=>'mailpasswordreset'], $mailData, function($message) {
        $message->to($this->email, 'Knowlegedepot password reset')->subject('Knowlegedepot password reset');
        $message->from('petro@knowledgedepot.ca','Knowlegedepot administrator');
        });
    }
}
