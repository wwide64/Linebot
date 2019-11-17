<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Line\LineBot;
use Line\LineBot\HTTPClient\CurlHttpClient;

class LineBotController extends Controller
{
    public function index()
    {
        return view('linebot.index');
    }

    public function parrot(Request $request)
    {
        // ログの出力
        Log::debug($request->header());
        Log::debug($request->input());

        // LINEBotのインスタンス化
        $httpClient = new CurlHttpClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        // 署名の検証を行う
        $signature = $request->header('x-line-signature');
        if(!$lineBot->validateSignature($request->getContent(), $signature)) {
            abort(400, 'Invalid signature');
        }

        // リクエストからイベント情報を取得
        $events = $lineBot->parseEventRequest($request->getContent(), $signature);

        Log::debug($events);

    }
}
