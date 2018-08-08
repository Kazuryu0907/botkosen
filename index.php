<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once dirname(__FILE__) .'/simplehtmldom_1_5/simple_html_dom.php';
$storage_file_path = dirname(__FILE__) . "/test.json";

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
		  $space_ignored = str_replace(" ", "",$Gettext);
		  $space_ignoreds = str_replace("、", ",",$space_ignored);
		  $random = explode(",",$space_ignoreds);
		  if($random[0] == "!w" && count($random) == 2){
			
			$line = $random[1];
			$text = file("test.txt");
			$count = count($text);
			$num = $count + 1;
			
			$fp = fopen('test.txt','a');
			fwrite($fp,"[".$num."]".$line."\r\n");
			fclose($fp);
			$bot->replyText($event->getReplyToken(),"メモを追加しました！"."'".$line."'");
		  }
		  if($Gettext == "Memo"){
			$texts = file_get_contents('test.txt');
			if(empty($texts) == false){
			$bot->replyText($event->getReplyToken(),$texts);
			}else{
				$bot->replyText($event->getReplyToken(),"メモがまだありません！");
			}
										}
		  if($random[0] == "!d" && count($random) == 2){
			$text = file("test.txt");
			$count = count($text);
			$hai = (int)$random[1];
			if($hai <= $count){
				$del = $text[$hai];
			unset($text[$hai]);
			file_put_contents('test.txt',$text);
			$Memos = file_get_contents('test.txt');
			replyMultiMessage($bot, $event->getReplyToken(),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("次のメモを消去しました!↓"),
											new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($del));
			}else{
				$bot->replyText($event->getReplyToken(),"第二引数が無効です！");
			}

			/*$pattern = $random[1];
			for($i = 0;$i <=count($text)- 1;$i++){
				$pos = strpos($text[$i],$pattern);
				if($pos !== false){
					unset($text[$i]);
					file_put_contents('test.txt',$text);
					replyMultiMessage($bot, $event->getReplyToken(),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("メモを消去しました!"),
											new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
										break;
					
				}
			}*/
		  }
			/*$fp = fopen('test.txt','r');
			while(!feof($fp)){
				$txt = fgets($fp);
				$alltext .= $txt.",";
			}
			$bot->replyText($event->getReplyToken(),$alltext);
			fclose($fp);  */
		
  		if ($random[0] == "Random") {
  			
  			//$calums = (int)random[1];
  		    if(is_numeric($random[1])){
  		    	
  		    	$num = (int)$random[1];
  			
  			//$num = (int)$calmus;
  		
  			$para = rand(1,$num);
				$fn = $para+= 2;
				$fn = (int)$fn;
  		    
  		    replyMultiMessage($bot, $event->getReplyToken(),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Random is running"),
											new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($random[1]),
											new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("選ばれたのは・・・"),
											new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($random[rand(1,$num)+2]));
    									//new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($fn));
    									
  		
  			//$bot->replyText($event->getReplyToken(), $para);
  			
  			
       			//$bot->replyText($event->getReplyToken(), $random[$para]);
}else{
	$bot->replyText($event->getReplyToken(),"無効な値です");
}

	
	}




  
			
						switch($Gettext)
						{
						case "!help":
						//$bot->replyText($event->getReplyToken(),"'Kosen'で高専ＨＰの更新をチェックできます".'\n'."荒らし行為はやめましょうby kazuryu" );
						replyMultiMessage($bot, $event->getReplyToken(),
											new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("'Kosen'で高専ＨＰの更新を確認できます"),
											new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("運行情報確認:'Keihan'(京阪本線・鴨東線).'Mono(大阪モノレール線)'.'Metro'(大阪メトロ御堂筋線).'Jr'(学研都市線).'JrT'(JR東西線).'Minou'(阪急箕面線).'Takara'(阪急宝塚本線)"),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("'Keihanchien'で京阪の遅延証明書が発行されているか確認できます"));
						break;
						/*
						case "!@everyone":
							$cut = $Gettext;
								//$cut = str_replace('!@everyone','',$cut);
									replyMultiMessage($bot, $event->getReplyToken(),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("!@everyone was called."),
    									new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($cut));
    				 				 		//$bot->replyText($event->getReplyToken(), "Everyone!");
						break;
						
						case "Yaju":
						replyImageMessage($bot, $event->getReplyToken(), "https://" . $_SERVER["HTTP_HOST"] . 
				"/imgs/original.jpg", "https://" . $_SERVER["HTTP_HOST"] . "/imgs/preview.jpg");
						break;
						
						case "Hira":
						replyImageMessage($bot, $event->getReplyToken(), "https://" . $_SERVER["HTTP_HOST"] . 
				"/imgs/hira.jpg", "https://" . $_SERVER["HTTP_HOST"] . "/imgs/hira.jpg");
						break;
						
						case "Nakahata":
						replyImageMessage($bot, $event->getReplyToken(), "https://" . $_SERVER["HTTP_HOST"] . 
				"/imgs/original.jpg", "https://" . $_SERVER["HTTP_HOST"] . "/imgs/preview.jpg");
						break;
					*/
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

					/*case "やるんだ":
					
						$bot->replyText($event->getReplyToken(),"やりますやります！！" );
						break;
						case "じゃあオナニー、とかっていうのは？":
						$bot->replyText($event->getReplyToken(),"やりますねぇ！" );
						break;

						
					*/
						case "Keihanchien":
						$year = date("Y");
						$month = date("m");
						$day = date("d");
					
						$URL = "https://www.keihan.co.jp/traffic/traintraffic/delay/detail/".$year.$month.$day."_001_001.html";
						$respon = @file_get_contents($URL,NULL,NULL,0,1);
						if($respon !== false){
							replyMultiMessage($bot, $event->getReplyToken(),
							new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("遅延証明書を発行します！"),
							new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($URL));
						}else{
						$bot->replyText($event->getReplyToken(),"遅延情報はありません");
						}
						break;
						case "Keihan":
						$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/300/0/");
							preg_match('/(<span class="icnNormalLarge">)(.*)(<dd class="normal">)/is', $html, $return);
							$return = str_replace('</span>', '', $return);
							$return = str_replace('</dt>', '', $return);
							$bot->replyText($event->getReplyToken(), "京阪本線・鴨東線".$return[2]);
							break;
							case "Mono":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/380/0/");
							preg_match('/(<span class="icnNormalLarge">)(.*)(<dd class="normal">)/is', $html, $return);
							$return = str_replace('</span>', '', $return);
							$return = str_replace('</dt>', '', $return);
							$bot->replyText($event->getReplyToken(), "大阪モノレール線".$return[2]);
							break;

							case "Metro":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/321/0/");
							preg_match('/(<span class="icnNormalLarge">)(.*)(<dd class="normal">)/is', $html, $return);
							$return = str_replace('</span>', '', $return);
							$return = str_replace('</dt>', '', $return);
							$bot->replyText($event->getReplyToken(), "大阪メトロ御堂筋線".$return[2]);
							break;
							
							case "Jr":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/271/0/");
								preg_match('/(<span class="icnNormalLarge">)(.*)(<dd class="normal">)/is', $html, $return);
								$return = str_replace('</span>', '', $return);
								$return = str_replace('</dt>', '', $return);
								$bot->replyText($event->getReplyToken(), "学研都市線".$return[2]);
								break;

								case "JrT":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/272/0/");
								preg_match('/(<span class="icnNormalLarge">)(.*)(<dd class="normal">)/is', $html, $return);
								$return = str_replace('</span>', '', $return);
								$return = str_replace('</dt>', '', $return);
								$bot->replyText($event->getReplyToken(), "JR東西線".$return[2]);
								break;
								
								case "Minou":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/312/0/");
								preg_match('/(<span class="icnNormalLarge">)(.*)(<dd class="normal">)/is', $html, $return);
								$return = str_replace('</span>', '', $return);
								$return = str_replace('</dt>', '', $return);
								$bot->replyText($event->getReplyToken(), "阪急箕面線".$return[2]);
								break;

								case "Takara":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/311/0/");
								preg_match('/(<span class="icnNormalLarge">)(.*)(<dd class="normal">)/is', $html, $return);
								$return = str_replace('</span>', '', $return);
								$return = str_replace('</dt>', '', $return);
								$bot->replyText($event->getReplyToken(), "阪急宝塚本線".$return[2]);
								break;
//$mojiretu = mb_substr($bun, ($iti=(mb_strpos($bun,'<div id="mdServiceStatus">')+1)), (mb_strpos($bun,'</div><!--/#mdServiceStatus-->'))-$iti);

								case "R":
								



					//���ׂĈႤ�ꍇ
					default:
						 //$bot->replyText($event->getReplyToken(), $event->getText());
						}
  
					}

					
 ?>
