<?php

namespace SynergiTech\Postal\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function process(Request $request)
    {
        // todo verify signature https://github.com/atech/postal/issues/432#issuecomment-353143578

        if ($request->input('payload.message') === null) {
            // todo remove link header
            return response('No payload', 400);
        }

        $emailmodel = config('postal.models.email');
        $webhookmodel = config('postal.models.webhook');

        $emailmodel = new $emailmodel;
        $webhookmodel = new $webhookmodel;

        $email = $emailmodel
            ->where('postal_id', $request->input('payload.message.id'))
            ->where('postal_token', $request->input('payload.message.token'))
            ->first();

        // we aren't concerned about not matching an email, don't visibly error
        if (is_object($email)) {
            $webhookmodel->email_id = $email->id;
            $webhookmodel->action = $request->input('event');
            $webhookmodel->payload = json_encode($request->input('payload'));
            $webhookmodel->save();
        }

        // todo remove link header
        return response('', 200);
    }
}
