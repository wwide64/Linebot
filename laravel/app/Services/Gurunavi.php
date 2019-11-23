<?php
namespace App\Services;

use GuzzleHttp\Client;

class Gurunavi
{
    // ぐるなびAPIのURLを定数に代入
    private const RESTAURANTS_SEARCH_API_URL = 'https://api.gnavi.co.jp/RestSearchAPI/v3/';

    // 挙動を安定させるため、引数と返り値に型を指定
    public function searchRestaurants(string $word): array
    {
        // GuzzleのClientクラスをインスタンス化
        $client = new Client;
        $response = $client
            // ぐるなびAPIの定数を取得
            ->get(self::RESTAURANTS_SEARCH_API_URL, [
                'query' => [
                    // GURUNAVI_ACCESS_KEYの値をセット
                    'keyid' => env('GURUNAVI_ACCESS_KEY'),
                    // 半角の空白を半角のカンマに置き換える
                    'freeword' => str_replace(' ', ',', $word),
                ],
                // エラー系のレスポンスでも例外を発生させないようにする
                'http_errors' => false,
            ]);
        
            // レスポンスボディを取得し、JSON形式から連想配列に変換
        return json_decode($response->getBody()->getContents(), true);
    }
}