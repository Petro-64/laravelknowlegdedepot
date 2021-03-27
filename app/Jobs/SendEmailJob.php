<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $name;
    private $token;
    private $email;
    private $projectURL;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $token, $email, $projectURL)
    {
        $this->name = $name;
        $this->token = $token;
        $this->email = $email;
        $this->projectURL = $projectURL;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        $mailData = array('name'=>$this->name, 'token'=>$this->token, 'projectURL'=>$this->projectURL);
        Mail::send(['html'=>'mail'], $mailData, function($message) {
        $message->to($this->email, 'Knowlegedepot signup')->subject('Knowlegedepot signup');
        $message->from('petro@knowledgedepot.ca','Knowlegedepot administrator');
        });
    }
}
