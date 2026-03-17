<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class MailchimpService
{
    protected $client;
    protected $apiKey;
    protected $listId;
    protected $server;

    public function __construct()
    {
        $this->apiKey = env('MAILCHIMP_API_KEY');
        $this->listId = env('MAILCHIMP_LIST_ID');
        $this->server = env('MAILCHIMP_SERVER');

        $this->client = new Client([
            'base_uri' => "https://{$this->server}.api.mailchimp.com/3.0/",
        ]);
    }

    public function addSubscriber($email, $name = '')
    {
        $subscriberHash = md5(strtolower($email));

        try {

            $response = $this->client->put(
                "lists/{$this->listId}/members/{$subscriberHash}",
                [
                    'auth' => ['anystring', $this->apiKey],
                    'json' => [
                        'email_address' => $email,
                        'status_if_new' => 'subscribed',
                        'merge_fields' => [
                            'FNAME' => $name,
                        ],
                    ],
                ]
            );

            return json_decode($response->getBody(), true);

        } catch (ClientException $e) {

            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            return [
                'status' => false,
                'message' => $body['detail'] ?? $e->getMessage(),
                'errors'  => $body['errors'] ?? [],
            ];

        } catch (\Exception $e) {

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}