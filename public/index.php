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
 

function getData($url2){

    $client2 = curl_init($url2);
    curl_setopt($client2,CURLOPT_RETURNTRANSFER,true);
    $response2 = curl_exec($client2);

    return json_decode($response2);
};

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
                    $textMsg = strtolower($event['message']['text']);
                    // send same message as reply to user
                    
                    if($textMsg=='halo'||$textMsg=='hai'||$textMsg=='hi'||$textMsg=='tes'||$textMsg=='test'){
                        // $textMessageBuilder1 = new TextMessageBuilder("Halo! Perkenalkan aku Pico. Aku adalah Pusat Informasi Covid-19. Kamu bisa tanya-tanya mengenai info terkait Covid-19 ke aku. Semoga kamu sehat-sehat selalu ya.\n\nUntuk memulai obrolan, kamu bisa ketik 'Tanya Pico' atau pilih menu di bawah ya.");
                        // $textMessageBuilder2 = new TextMessageBuilder("Bagikan info akurat tentang COVID-19 ke teman dan keluargamu 🙏\nUntuk info lebih lengkap kamu bisa kunjugi https://www.covid19.go.id\nHotline 119 untuk mendapatkan bantuan apabila ada gejala");

                        // $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
                        
                        // $multiMessageBuilder = new MultiMessageBuilder();
                        // $multiMessageBuilder->add($textMessageBuilder1);
                        // $multiMessageBuilder->add($textMessageBuilder2);
                        // $multiMessageBuilder->add($stickerMessageBuilder);
                        
                        // $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                        $greetingMsg = "Halo! Perkenalkan aku Pico. Aku adalah Pusat Informasi Covid-19. Kamu bisa tanya-tanya mengenai info terkait Covid-19 ke aku. Semoga kamu sehat-sehat selalu ya.\n\nUntuk memulai obrolan, kamu bisa ketik 'Tanya Pico' atau pilih menu di bawah ya.";

                        $flexTemplate = file_get_contents("../vendor/greetings_card.json");
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'text',
                                    'text'  => $greetingMsg
                                ],
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Test Flex Message',
                                    'contents' => json_decode($flexTemplate)
                                ]

                            ],
                        ]);
                    }else if($textMsg=='tanya pico'){
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
                    }else if($textMsg=='kabar covid-19 terkini di indonesia'){

                        $url = "https://api.kawalcorona.com/indonesia/";

                        $client = curl_init($url);
                        curl_setopt($client,CURLOPT_RETURNTRANSFER,true);
                        $response = curl_exec($client);

                        $result = json_decode($response);

                        $natConfirmed = $result[0]->positif;

                        $natDeaths = $result[0]->meninggal;

                        $natRecovered = $result[0]->sembuh;

                        $intConfirmed = getData("https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/Coronavirus_2019_nCoV_Cases/FeatureServer/1/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22Confirmed%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true");

                        $intDeaths = getData("https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/Coronavirus_2019_nCoV_Cases/FeatureServer/1/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22Deaths%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true");

                        $intRecovered = getData("https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/Coronavirus_2019_nCoV_Cases/FeatureServer/1/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22Recovered%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true");

                        $interConfirmed = $intConfirmed->features[0]->attributes->value;
                        $interDeaths = $intDeaths->features[0]->attributes->value;
                        $interRecovered = $intRecovered->features[0]->attributes->value;

                        $flexTemplate = '{
                            "type": "carousel",
                            "contents": [
                              {
                                "type": "bubble",
                                "direction": "ltr",
                                "header": {
                                  "type": "box",
                                  "layout": "vertical",
                                  "contents": [
                                    {
                                      "type": "text",
                                      "text": "Global",
                                      "align": "center",
                                      "weight": "bold"
                                    }
                                  ]
                                },
                                "hero": {
                                  "type": "image",
                                  "url": "https://scontent-sin6-2.xx.fbcdn.net/v/t1.0-9/s960x960/93109875_699684144103306_7846826673365319680_o.jpg?_nc_cat=105&_nc_sid=e007fa&_nc_ohc=3h0wz7cKk08AX8loWuB&_nc_ht=scontent-sin6-2.xx&_nc_tp=7&oh=a4e51183a95a9d223507684efb5b45a6&oe=5EBAF173",
                                  "size": "full",
                                  "aspectRatio": "1.51:1",
                                  "aspectMode": "fit"
                                },
                                "body": {
                                  "type": "box",
                                  "layout": "vertical",
                                  "contents": [
                                    {
                                      "type": "box",
                                      "layout": "horizontal",
                                      "contents": [
                                        {
                                          "type": "text",
                                          "text": "Kasus Terkonfirmasi",
                                          "flex": 3,
                                          "align": "start",
                                          "color": "#C4C4C4"
                                        },
                                        {
                                          "type": "filler"
                                        },
                                        {
                                          "type": "text",
                                          "text": "'.$interConfirmed.'",
                                          "flex": 1,
                                          "align": "end"
                                        }
                                      ]
                                    },
                                    {
                                      "type": "box",
                                      "layout": "horizontal",
                                      "contents": [
                                        {
                                          "type": "text",
                                          "text": "Sembuh",
                                          "flex": 3,
                                          "align": "start",
                                          "color": "#C4C4C4"
                                        },
                                        {
                                          "type": "filler"
                                        },
                                        {
                                          "type": "text",
                                          "text": "'.$interRecovered.'",
                                          "flex": 1,
                                          "align": "end"
                                        }
                                      ]
                                    },
                                    {
                                      "type": "box",
                                      "layout": "horizontal",
                                      "contents": [
                                        {
                                          "type": "text",
                                          "text": "Kematian",
                                          "flex": 3,
                                          "align": "start",
                                          "color": "#C4C4C4"
                                        },
                                        {
                                          "type": "filler"
                                        },
                                        {
                                          "type": "text",
                                          "text": "'.$interRecovered.'",
                                          "flex": 1,
                                          "align": "end"
                                        }
                                      ]
                                    }
                                  ]
                                },
                                "footer": {
                                  "type": "box",
                                  "layout": "vertical",
                                  "contents": [
                                    {
                                      "type": "text",
                                      "text": "Sumber: https://kawalcorona.com/",
                                      "align": "center",
                                      "color": "#3F3D56"
                                    },
                                    {
                                      "type": "button",
                                      "action": {
                                        "type": "uri",
                                        "label": "Kunjungi Sumber",
                                        "uri": "https://kawalcorona.com/"
                                      },
                                      "color": "#F8BD01",
                                      "margin": "md",
                                      "height": "sm",
                                      "style": "primary"
                                    }
                                  ]
                                }
                              },
                              {
                                "type": "bubble",
                                "direction": "ltr",
                                "header": {
                                  "type": "box",
                                  "layout": "vertical",
                                  "contents": [
                                    {
                                      "type": "text",
                                      "text": "Nasional",
                                      "align": "center",
                                      "weight": "bold"
                                    }
                                  ]
                                },
                                "hero": {
                                  "type": "image",
                                  "url": "https://scontent-sin6-2.xx.fbcdn.net/v/t1.0-9/s960x960/92516455_699684117436642_1982139746545041408_o.jpg?_nc_cat=103&_nc_sid=e007fa&_nc_ohc=aPIVi2ko7jcAX-GFbhe&_nc_ht=scontent-sin6-2.xx&_nc_tp=7&oh=734031b4c9f040f950281169d5889608&oe=5EB77B49",
                                  "margin": "md",
                                  "size": "full",
                                  "aspectRatio": "1.51:1",
                                  "aspectMode": "fit"
                                },
                                "body": {
                                  "type": "box",
                                  "layout": "vertical",
                                  "contents": [
                                    {
                                      "type": "box",
                                      "layout": "horizontal",
                                      "contents": [
                                        {
                                          "type": "text",
                                          "text": "Kasus Terkonfirmasi",
                                          "flex": 3,
                                          "align": "start",
                                          "color": "#C4C4C4"
                                        },
                                        {
                                          "type": "filler"
                                        },
                                        {
                                          "type": "text",
                                          "text": "'.$natConfirmed.'",
                                          "flex": 1,
                                          "align": "end"
                                        }
                                      ]
                                    },
                                    {
                                      "type": "box",
                                      "layout": "horizontal",
                                      "contents": [
                                        {
                                          "type": "text",
                                          "text": "Sembuh",
                                          "flex": 3,
                                          "align": "start",
                                          "color": "#C4C4C4"
                                        },
                                        {
                                          "type": "filler"
                                        },
                                        {
                                          "type": "text",
                                          "text": "'.$natRecovered.'",
                                          "flex": 1,
                                          "align": "end"
                                        }
                                      ]
                                    },
                                    {
                                      "type": "box",
                                      "layout": "horizontal",
                                      "contents": [
                                        {
                                          "type": "text",
                                          "text": "Kematian",
                                          "flex": 3,
                                          "color": "#C4C4C4"
                                        },
                                        {
                                          "type": "filler"
                                        },
                                        {
                                          "type": "text",
                                          "text": "'.$natDeaths.'",
                                          "flex": 1,
                                          "align": "end"
                                        }
                                      ]
                                    }
                                  ]
                                },
                                "footer": {
                                  "type": "box",
                                  "layout": "vertical",
                                  "contents": [
                                    {
                                      "type": "text",
                                      "text": "Sumber: https://kawalcorona.com/",
                                      "align": "center",
                                      "color": "#3F3D56"
                                    },
                                    {
                                      "type": "button",
                                      "action": {
                                        "type": "uri",
                                        "label": "Kunjungi Sumber",
                                        "uri": "https://kawalcorona.com/"
                                      },
                                      "color": "#F8BD01",
                                      "margin": "md",
                                      "height": "sm",
                                      "style": "primary"
                                    }
                                  ]
                                }
                              }
                            ]
                        }';

                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'text',
                                    'text'  => 'Situasi virus corona (COVID-19)'
                                ],
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Test Flex Message',
                                    'contents' => json_decode($flexTemplate)
                                ]

                            ],
                        ]);
                        
                        // $message="Situasi virus corona (COVID-19) ";
                        // $message.="\nGlobal";
                        // $message.="\nKasus Terkonfirmasi: ".$interConfirmed;
                        // $message.="\nSembuh: ".$interRecovered;
                        // $message.="\nKematian: ".$interDeaths;
                        // $message.="\nNasional";
                        // $message.="\nKasus Terkonfirmasi: ".$natConfirmed;
                        // $message.="\nSembuh: ".$natRecovered;
                        // $message.="\nKematian: ".$natDeaths;
                        // $message.="\nSumber: https://kawalcorona.com/'
                        // ";
                        // $message.="\nUntuk info peta sebaran COVID-19 bisa klik link berikut https://www.covid19.go.id/situasi-virus-corona/";
                        // $result = $bot->replyText($event['replyToken'], $message);
                    }else if($textMsg=='sebenarnya apa sih covid-19 itu?'){
                        $message="Penyakit Coronavirus 2019 ( COVID-19 ) adalah penyakit menular yang disebabkan oleh sindrom pernapasan akut coronavirus 2 (SARS-CoV-2). Penyakit ini pertama kali diidentifikasi pada Desember 2019 di Wuhan, ibu kota provinsi Hubei China, dan sejak itu menyebar secara global, mengakibatkan pandemi koronavirus 2019-20 yang sedang berlangsung.";
                        $result = $bot->replyText($event['replyToken'], $message);
                    }else if($textMsg=='apa saja gejala covid-19?'){
                        $message="Secara umum ada 3 gejala umum yang bisa menandakan seseorang terinfeksi virus Corona, yaitu:

                        Demam (suhu tubuh di atas 38 derajat Celsius)
                        Batuk
                        Sesak napas
                        Menurut penelitian, gejala COVID-19 muncul dalam waktu 2 hari sampai 2 minggu setelah terpapar virus Corona.";
                        $result = $bot->replyText($event['replyToken'], $message);
                    }else if($textMsg=='bagaimana cara melindungi diri?'){
                        $message="Tunjukkan aksimu! 

                        Lindungi diri. Lindungi sesama.
                        
                        Apa AKSI nyata yang dapat kamu lakukan? 
                        
                        ✅ Tetap di rumah. Bekerja, belajar dan beribadah di rumah
                        
                        ✅ Jaga jarak minimal 1 meter dengan orang di lain
                        
                        ✅ Jangan kontak langsung dengan orang bergejala COVID-19. Lakukan komunikasi via telepon, chat atau video call
                        
                        ✅ Hindari kerumunan
                        
                        ✅ Jangan sentuh mata, hidung dan mulut
                        
                        ✅ Selalu cuci tangan pakai sabun dan air mengalir! Sebelum makan dan menyiapkan makanan, setelah dari toilet, setelah memegang binatang dan sehabis berpergian
                        
                        ✅ Ketika batuk atau bersin, tutup mulut dan hidung dengan siku terlipat atau tisu. Buang langsung tisu ke tempat sampah setelah digunakan
                        
                        ✅ Beritahu petugas kesehatan jika kamu mengalami gejala, pernah kontak erat dengan orang bergejala atau bepergian ke wilayah terjangkit COVID-19
                        
                        ✅ Jika petugas kesehatan menyatakan kamu harus isolasi diri, maka patuhi agar lekas sembuh dan tidak menulari orang lain
                        
                        ✅ Bersikaplah terbuka tentang statusmu pada orang lain di sekitar. Ini adalah bentuk nyata kepedulianmu pada diri sendiri dan sesama";
                        $result = $bot->replyText($event['replyToken'], $message);
                    }else if($textMsg=='bagaimana cara melindungi orang lain?'){
                        $message="Yang bisa kamu lakukan untuk melindungi orang-orang terdekatmu dari Covid-19, yaitu:

                        ✅Saat kamu batuk atau bersin, jangan lupa untuk menjauh dan menutup mulut serta  hidung kamu dengan tissue, saputangan, atau lipatan siku.
                        
                        ✅Segera membuang tisu atau masker yang telah kamu gunakan ke tempat sampah. 
                        
                        ✅Jangan lupa untuk merobek masker yang telah digunakan ya, untuk mencegah penggunaan ulang masker. 
                        
                        ✅Jangan lupa untuk mencuci tanganmu dengan sabun setelah batuk atau bersin. 
                        
                        ✅Jangan meludah disembarang tempat
                        
                        ✅Segera menghubungi Rumah Sakit rujukan bila orang terdekatmu mengalami gejala Covid-19 dengan menghubungi 119";
                        $result = $bot->replyText($event['replyToken'], $message);
                    }else if($textMsg=='masker perlu gak sih?'){
                        $message="Semua orang harus menggunakan masker kalau terpaksa beraktivitas di luar rumah.

                        Kamu bisa menggunakan masker kain tiga lapis yang dapat dicuci dan digunakan berkali-kali, agar masker bedah dan N-95 yang sekali pakai bisa ditujukan untuk petugas medis.
                        
                        Jangan lupa untuk mencuci masker kain menggunakan air sabun agar tetap bersih dan efektif untuk mencegah penyebaran virus COVID-19.";
                        $result = $bot->replyText($event['replyToken'], $message);
                    }else if($textMsg=='rumah sakit rujukan covid-19'){
                        $url = "https://api.kawalcorona.com/indonesia/";

                        $client = curl_init($url);
                        curl_setopt($client,CURLOPT_RETURNTRANSFER,true);
                        $response = curl_exec($client);

                        $result = json_decode($response);

                        $Confirmed = $result[0]->positif;

                        $Deaths = $result[0]->meninggal;

                        $Recovered = $result[0]->sembuh;

                        $datetimeString = $result[1]->lastupdate;
                        $Last_Update = date("l d F Y, H:i:s", strtotime($datetimeString));

                        $message="Konfirmasi:".$Confirmed;
                        $result = $bot->replyText($event['replyToken'], $message);
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