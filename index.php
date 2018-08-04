<?php

require_once __DIR__ . '/vendor/autoload.php';


$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('LINE_BOT_CHANNEL_TOKEN'));
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('LINE_BOT_CHANNEL_SECRET')]);


$signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log("parseEventRequest failed. InvalidSignatureException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log("parseEventRequest failed. UnknownEventTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log("parseEventRequest failed. UnknownMessageTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log("parseEventRequest failed. InvalidEventRequestException => ".var_export($e, true));
}

//Multi
function replyMultiMessage($bot, $replyToken, ...$msgs) {
  $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
  foreach($msgs as $value) {
    $builder->add($value);
  }
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

function replyImageMessage($bot, $replyToken, $originalImageUrl, $previewImageUrl) {
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}
$Hnum = 0;

foreach ($events as $event) {
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    error_log('Non message event has come');
    continue;
  }
  	if (!($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
    	error_log('Non text message has come');
    	continue;
  		}
  		$Gettext = (string)$event->getText();
  			
  		
			
						switch($Gettext)
						{
						case "!help":
						//$bot->replyText($event->getReplyToken(),"'Kosen'で高専ＨＰの更新をチェックできます".'\n'."荒らし行為はやめましょうby kazuryu" );
						replyMultiMessage($bot, $event->getReplyToken(),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("'Kosen'で高専ＨＰの更新をチェックできます"),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("荒らし行為はやめましょうby kazuryu"));
						break;
						
						
						case "Kosen":
						$year = date("Y").'/';
						$month = date("m").'/';
						$day = date("d").'/';
						$URL = "https://www2.ct.osakafu-u.ac.jp/".$year.$month.$day;
					
						$respon = @file_get_contents($URL,NULL,NULL,0,1);
						if($respon !== false){
							replyMultiMessage($bot, $event->getReplyToken(),
							new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("更新があります！"),
							new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($URL));
						}else{
						$bot->replyText($event->getReplyToken(),"更新はありません");
						}
						break;

						
						
						
					//���ׂĈႤ�ꍇ
					default:
						 //$bot->replyText($event->getReplyToken(), $event->getText());
						}
  
  
}

 ?>