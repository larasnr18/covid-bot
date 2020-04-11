<?php
require __DIR__ . '/../vendor/autoload.php';
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "HtN7CnrRMnzgkTRpzlDJGpuqtspaqwcgN4jq8K+qDReGRVqhk5aUQ7MwVArYKf4HvzcoHSR815gm0ujYCZNlW/w+icZzP9UN5rMW9iMp+J0aeqzOYvrwhbHrqHmD0Imb5l8RMz9PFHppgZYTy21oKwdB04t89/1O/w1cDnyilFU=";
$channel_secret = "7279ab6761cf8816c11c69b05810e34c";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$app = AppFactory::create();
$app->setBasePath("/public");
 
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});
 
// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);
 
    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }
    
// kode aplikasi nanti disini
$data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                $msgType= $event['message']['type'];
                if($msgType == 'text')
                {
                    $textMsg = $event['message']['text'];
                    // send same message as reply to user
                    
                    if($textMsg=='halo'){
                      $message="Halo! Selamat datang di Pusat Informasi Covid-19 powered by Kemkominfo RI. Semoga kamu sehat-sehat selalu.";
                      $message .= "Apa saja sih yang ingin kamu ketahui mengenai Covid-19?

                      A. Kabar Covid-19 terkini di Indonesia
                      B. Sebenarnya apa sih Covid-19 itu?
                      C. Apa saja gejala Covid-19?
                      D. Bagaimana cara melindungi diri?
                      E. Bagaimana cara melindungi orang lain? 
                      F. Masker perlu gak sih?
                      G. Rumah Sakit Rujukan Covid-19.
                      
                      Ketik A, B, C, D, E, F, atau G, lalu kirim ke kami. Maka, kami akan menjawab pertanyaan kamu. 
                      
                      Bagikan info akurat tentang COVID-19 ke teman dan keluargamu 🙏
                      https://www.covid19.go.id
                      0811 333 99 000
                      
                      Mari saling melindungi dari virus corona dengan mengunduh aplikasi pedulilindungi di www.pedulilindungi.id
                      
                      Hotline 119 untuk mendapatkan bantuan apabila ada gejala
                      
                      #LawanBersamaCovid19
                      #DiRumahAja
                      #JagaJarak
                      #MaskerUntukSemua";
                      $result = $bot->replyText($event['replyToken'], $message);
                    }else if($textMsg=='menu'){
                        $flexTemplate = file_get_contents("../vendor/menu.json");
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Test Flex Message',
                                    'contents' => json_decode($flexTemplate)
                                ]
                            ],
                        ]);
                    }
 
 
                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
 
 
                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                }
            }
        }
    }
 
});
$app->run();