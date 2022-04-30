<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $api_key = 'e973303d149fd43596aadda0dfd42808';
    private $api_key_secret = 'c2ac8950ced277d7f2c681b222148f49';

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
            $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "glippa.sebastien@gmail.com",
                        'Name' => "Breizh Gyotaku"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3887431,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success(); // && dd($response->getData());
    }
}