<?php

namespace SynergiTech\Postal\Tests\Controllers;

use SynergiTech\Postal\Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    public function testNoPayloadReturns404()
    {
        $response = $this->postJson('/postal/webhook');

        $response->assertStatus(400);
        $response->assertSee('No payload');
    }

    public function testWebhookVerificationFailure()
    {
        $input = [
            'payload' => [
                'message' => [],
            ]
        ];
        $response = $this
            ->withHeaders(['x-postal-signature' => 'invalid'])
            ->postJson('/postal/webhook', $input);

        $response->assertStatus(400);
        $response->assertSee('Unable to match signature header');
    }

    public function testWebhookReceivedForNonexistentEmail()
    {
        $input = [
            'payload' => [
                'message' => [
                    'id' => 'z',
                    'token' => 'z',
                ],
            ],
        ];

        $body = json_encode($input);
        $signed = openssl_sign($body, $sig, $this->getKeyPair()['private'], OPENSSL_ALGO_SHA1);

        $this->assertTrue($signed);

        $response = $this
            ->withHeaders(['x-postal-signature' => base64_encode($sig)])
            ->postJson('/postal/webhook', $input);

        $response->assertOk();
    }

    public function testWebhookReceivedSuccessfully()
    {
        $input = [
            'payload' => [
                'message' => [
                    'id' => 'a',
                    'token' => 'a',
                ],
            ],
            'event' => 'unit.test',
        ];

        $emailModel = config('postal.models.email');
        $email = new $emailModel();
        $email->to_email = 'example@example.org';
        $email->from_email = 'example@example.org';
        $email->postal_id = 'a';
        $email->postal_email_id = 'a';
        $email->postal_token = 'a';
        $email->save();

        $body = json_encode($input);
        $signed = openssl_sign($body, $sig, $this->getKeyPair()['private'], OPENSSL_ALGO_SHA1);

        $this->assertTrue($signed);

        $response = $this
            ->withHeaders(['x-postal-signature' => base64_encode($sig)])
            ->postJson('/postal/webhook', $input);

        $response->assertOk();

        $webhookModel = config('postal.models.webhook');
        $webhook = $webhookModel::where('email_id', $email->id)->first();

        $this->assertSame('unit.test', $webhook->action);

        $this->assertEquals([$webhook], $email->webhooks()->get()->all());
        $this->assertTrue($webhook->email()->first()->is($email));
        $webhook->delete();
        $email->delete();
    }
}