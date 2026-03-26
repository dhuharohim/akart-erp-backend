<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\XenditService;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function __construct(private XenditService $xendit) {}

    public function handle(Request $request)
    {
        $callbackToken = $request->header('x-callback-token', '');

        abort_if(
            ! $this->xendit->verifyWebhookToken($callbackToken),
            403,
            'Invalid callback token'
        );

        $this->xendit->handleCallback($request->all());

        return response()->json(['status' => 'ok']);
    }
}
