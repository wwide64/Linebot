<?php

namespace App\Http\Controllers;

use App\Services\Gurunavi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHttpClient;


class LineBotController extends Controller
{
    public function index()
    {
        return view('linebot.index');
    }

    // public function parrot(Request $request)
    public function restaurants(Request $request)
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

        // LINEのチャネルに返信する
        foreach($events as $event) {
            if(!($event instanceof TextMessage)) {
                Log::debug('Non text message has come');
                continue;
            }

            // Gurunaviクラスをインスタンス化して変数に代入
            $gurunavi = new Gurunavi();
            // ユーザーからのメッセージのテキストを取得してsearchRestaurantsメソッドに渡し、変数に代入
            $gurunaviResponse = $gurunavi->searchRestaurants($event->getText());

            // くるなびAPIのレスポンスがエラーだった場合の処理
            if (array_key_exists('error', $gurunaviResponse)) {
                $replyText = $gurunaviResponse['error'][0]['message'];
                $replyToken = $event->getReplyToken();
                $lineBot->replyText($replyToken, $replyText);
                continue;
            }

            $replyText = '';
            foreach($gurunaviResponse['rest'] as $restaurant) {
                $replyText .=
                    $restaurant['name'] . "\n" .
                    $restaurant['url'] . "\n" .
                    "\n";
            }

            $replyToken = $event->getReplyToken();
            // $replyText = $event->getText();  ←オウム返し用コード
            $lineBot->replyText($replyToken, $replyText);
        }

    }
}
