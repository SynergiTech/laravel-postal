<?php

namespace SynergiTech\Postal\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function process(Request $request)
    {
        if ($request->input('payload') === null) {
            // todo remove link header
            return response('No payload', 400);
        }

        if (config('postal.webhook.verify') === true && strlen(config('postal.webhook.public_key')) > 0) {
            $rsa_key_pem = "-----BEGIN PUBLIC KEY-----\r\n" .
                chunk_split(config('postal.webhook.public_key'), 64) .
                "-----END PUBLIC KEY-----\r\n";
            $rsa_key = openssl_pkey_get_public($rsa_key_pem);

            $signature = base64_decode($request->header('x-postal-signature'));

            $body = $request->getContent();

            $result = openssl_verify($body, $signature, $rsa_key, OPENSSL_ALGO_SHA1);

            if ($result !== 1) {
                return response('Unable to match signature header', 400);
            }
        }

        $emailmodel = config('postal.models.email');
        $webhookmodel = config('postal.models.webhook');

        $emailmodel = new $emailmodel;
        $webhookmodel = new $webhookmodel;

        if ($request->input('payload.message') !== null) {
            $postal_id = $request->input('payload.message.id');
            $postal_token = $request->input('payload.message.token');
        } elseif ($request->input('payload.original_message') !== null) {
            $postal_id = $request->input('payload.original_message.id');
            $postal_token = $request->input('payload.original_message.token');
        }

        if (isset($postal_id) && isset($postal_token)) {
            $email = $emailmodel
                ->where('postal_id', $postal_id)
                ->where('postal_token', $postal_token)
                ->first();

            // we aren't concerned about not matching an email, don't visibly error
            if (is_object($email)) {
                $webhookmodel->email_id = $email->id;
                $webhookmodel->action = $request->input('event');
                $webhookmodel->payload = json_encode($request->input('payload'));
                $webhookmodel->save();
            }
        }

        // todo remove link header
        return response('', 200);
    }
}
