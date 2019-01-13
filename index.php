<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once dirname(__FILE__) .'/simplehtmldom_1_5/simple_html_dom.php';
$storage_file_path = dirname(__FILE__) . "/test.json";
$request = file_get_contents('php://input');
$jsonObj = json_decode($request);
$content = $jsonObj->result{0}->content;
//$mb = mb_strlen($Gettext);

function DownloadDB(){
	$headers = array(
		"Authorization:Bearer fDO986b8w1AAAAAAAAAAFY7SxbaDd5IwAA0V8UO9vit3ayxm78Mh3ykC6i5OC_N7",
		'Dropbox-API-Arg:{"path":"/backUP.txt"}'
		);
	
	
	$url = "https://content.dropboxapi.com/2/files/download";
	$ch = curl_init(); // はじめ
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$html =  curl_exec($ch);
	curl_close($ch); //終了
	return $html;
}

function UploadDB($fullpath){
    $url = "https://content.dropboxapi.com/2/files/upload";
	$ch2 = curl_init($url);
	curl_setopt($ch2, CURLOPT_POST, TRUE); // POST
	curl_setopt($ch2, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
	$headers = array(
		"Authorization:Bearer fDO986b8w1AAAAAAAAAAFY7SxbaDd5IwAA0V8UO9vit3ayxm78Mh3ykC6i5OC_N7",
		'Content-Type: application/octet-stream',
		'Dropbox-API-Arg: {"path":"/backUP.txt"}'
		);
	curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch2, CURLOPT_POSTFIELDS, $fullpath);
	$html =  curl_exec($ch2);
	curl_close($ch2); //終了
}

function DelDB(){
	$headers = array(
		"Authorization:Bearer fDO986b8w1AAAAAAAAAAFY7SxbaDd5IwAA0V8UO9vit3ayxm78Mh3ykC6i5OC_N7",
		'Content-Type: application/json'
	
		);
	
	
	$url = "https://api.dropboxapi.com/2/files/delete_v2";
	$ch = curl_init(); // はじめ
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
	curl_setopt($ch, CURLOPT_POST, TRUE); // POST
	//ヘッダー追加オプション
	curl_setopt($ch, CURLOPT_POSTFIELDS,'{"path":"/backUP.txt"}');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$html =  curl_exec($ch);
	curl_close($ch); //終了
	return $html;
}
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
function replyLocationMessage($bot, $replyToken, $title, $address, $lat, $lon) {
	$response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $lat, $lon));
	if (!$response->isSucceeded()) {
	  error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
	}
  }
$Hnum = 0;

foreach ($events as $event) {
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    error_log('Non message event has come');
    continue;
  }if (($event instanceof \LINE\LINEBot\Event\MessageEvent\LocationMessage)) {
	$lat   = $event->getLatitude();
	$lon   = $event->getLongitude();
	$url = "https://aed.azure-mobile.net/api/NearAED?lat=".$lat."&lng=".$lon;
	$aeds = file_get_contents($url);
	$aeds = json_decode($aeds);
	$count = count($aeds);
	if($count == 0){
		$bot->replyText($event->getReplyToken(),"近くに登録されているAEDはありません・・");
	}elseif($count == 1){
		replyLocationMessage($bot, $event->getReplyToken(), $aeds[0]->LocationName, $aeds[0]->Perfecture.$aeds[0]->City.$aeds[0]->AddressArea, $aeds[0]->Latitude,$aeds[0]->Longitude);
	}elseif($count == 2){
		replyMultiMessage($bot, $event->getReplyToken(), 
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[0]->LocationName, $aeds[0]->Perfecture.$aeds[0]->City.$aeds[0]->AddressArea, $aeds[0]->Latitude,$aeds[0]->Longitude),
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[1]->LocationName, $aeds[1]->Perfecture.$aeds[1]->City.$aeds[1]->AddressArea, $aeds[1]->Latitude,$aeds[1]->Longitude)
	);
	}elseif($count == 3){
		replyMultiMessage($bot, $event->getReplyToken(), 
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[0]->LocationName, $aeds[0]->Perfecture.$aeds[0]->City.$aeds[0]->AddressArea, $aeds[0]->Latitude,$aeds[0]->Longitude),
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[1]->LocationName, $aeds[1]->Perfecture.$aeds[1]->City.$aeds[1]->AddressArea, $aeds[1]->Latitude,$aeds[1]->Longitude),
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[2]->LocationName, $aeds[2]->Perfecture.$aeds[2]->City.$aeds[2]->AddressArea, $aeds[2]->Latitude,$aeds[2]->Longitude)
	);
	}else{
		replyMultiMessage($bot, $event->getReplyToken(), 
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[0]->LocationName, $aeds[0]->Perfecture.$aeds[0]->City.$aeds[0]->AddressArea, $aeds[0]->Latitude,$aeds[0]->Longitude),
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[1]->LocationName, $aeds[1]->Perfecture.$aeds[1]->City.$aeds[1]->AddressArea, $aeds[1]->Latitude,$aeds[1]->Longitude),
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[2]->LocationName, $aeds[2]->Perfecture.$aeds[2]->City.$aeds[2]->AddressArea, $aeds[2]->Latitude,$aeds[2]->Longitude),
		new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($aeds[3]->LocationName, $aeds[3]->Perfecture.$aeds[3]->City.$aeds[3]->AddressArea, $aeds[3]->Latitude,$aeds[3]->Longitude)
	);
	}

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
		$hai = $hai -1;
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
										new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("'Keihanchien'で京阪の遅延証明書が発行されているか確認できます"),
										new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("個人チャットにおいて、位置情報を送信することで、周辺の公共のAEDの位置を検索します"),
										new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("'Memo'で現在記録されているメモを表示します  '!w,(メモしたい文)'(括弧はいらない)で、メモに文を追加できます  '!d,(消したいメモ番号)'(括弧はいらない)で指定したメモ番号のメモを消去できます"));
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
						preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
						$matches = str_replace('<dl>', '', $matches);
						$matches = str_replace('<dt>', '', $matches);
						$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
						$matches = str_replace('</span>', '', $matches);
						$matches = str_replace('<span>', '', $matches);
						$matches = str_replace('</dt>', '', $matches);
						$matches = str_replace('<p>', '', $matches);
						$matches = str_replace('</p>', '', $matches);
						$matches = str_replace('</dd>', '', $matches);
						$matches = str_replace('</dl>', '', $matches);
						$matches = str_replace('</div>', '', $matches);
						if(preg_match('<dd class="normal">',$matches[1])){
						$matches = str_replace('<dd class="normal">', '', $matches);
						$bot->replyText($event->getReplyToken(), "京阪本線・鴨東線".$matches[1]);
						}else{
							$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
											   $matches = str_replace('<span>', '', $matches);
											$bot->replyText($event->getReplyToken(), "京阪本線・鴨東線".$matches[1]);
						}


			 
					
						

							
							break;
							case "Mono":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/380/0/");
							preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
							$matches = str_replace('</span>', '', $matches);
							$matches = str_replace('</dt>', '', $matches);
							$matches = str_replace('<span>', '', $matches);
							$matches = str_replace('<p>', '', $matches);
							$matches = str_replace('</p>', '', $matches);
							$matches = str_replace('</dd>', '', $matches);
							$matches = str_replace('</dl>', '', $matches);
							$matches = str_replace('</div>', '', $matches);
							if(preg_match('<dd class="normal">',$matches[1])){
								$matches = str_replace('<dd class="normal">', '', $matches);
								$bot->replyText($event->getReplyToken(), "大阪モノレール線".$matches[1]);
							}else{
								$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
												$matches = str_replace('<span>', '', $matches);
													$bot->replyText($event->getReplyToken(), "大阪モノレール線".$matches[1]);
								}
 
 
				 
							
							
					
							break;

							case "Metro":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/321/0/");
							preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
							$matches = str_replace('</span>', '', $matches);
							$matches = str_replace('</dt>', '', $matches);
							$matches = str_replace('<p>', '', $matches);
							$matches = str_replace('<span>', '', $matches);
							$matches = str_replace('</p>', '', $matches);
							$matches = str_replace('</dd>', '', $matches);
							$matches = str_replace('</dl>', '', $matches);
							$matches = str_replace('</div>', '', $matches);
							if(preg_match('<dd class="normal">',$matches[1])){
								$matches = str_replace('<dd class="normal">', '', $matches);
								$bot->replyText($event->getReplyToken(), "大阪メトロ御堂筋線".$matches[1]);
							}else{
								$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
							$matches = str_replace('<span>', '', $matches);
											$bot->replyText($event->getReplyToken(), "大阪メトロ御堂筋線".$matches[1]);
							}
 
 
				 
							
							break;
							
							case "Jr":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/271/0/");
							preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
							$matches = str_replace('</span>', '', $matches);
							$matches = str_replace('</dt>', '', $matches);
							$matches = str_replace('<p>', '', $matches);
							$matches = str_replace('<span>', '', $matches);
							$matches = str_replace('</p>', '', $matches);
							$matches = str_replace('</dd>', '', $matches);
							$matches = str_replace('</dl>', '', $matches);
							$matches = str_replace('</div>', '', $matches);
							if(preg_match('<dd class="normal">',$matches[1])){
								$matches = str_replace('<dd class="normal">', '', $matches);
								$bot->replyText($event->getReplyToken(), "学研都市線".$matches[1]);
							}else{
								$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
												$matches = str_replace('<span>', '', $matches);
													$bot->replyText($event->getReplyToken(), "学研都市線".$matches[1]);
							}
							
 
				 
								
								break;

								case "JrT":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/272/0/");
							preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
							$matches = str_replace('</span>', '', $matches);
							$matches = str_replace('</dt>', '', $matches);
							$matches = str_replace('<p>', '', $matches);
							$matches = str_replace('<span>', '', $matches);
							$matches = str_replace('</p>', '', $matches);
							$matches = str_replace('</dd>', '', $matches);
							$matches = str_replace('</dl>', '', $matches);
							$matches = str_replace('</div>', '', $matches);
							if(preg_match('<dd class="normal">',$matches[1])){
								$matches = str_replace('<dd class="normal">', '', $matches);
								$bot->replyText($event->getReplyToken(), "JR東西線".$matches[1]);
							}else{
								$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
												$matches = str_replace('<span>', '', $matches);
													$bot->replyText($event->getReplyToken(), "JR東西線".$matches[1]);
							}
 
 
				 
								
								break;
								
								case "Minou":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/312/0/");
							preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
							$matches = str_replace('</span>', '', $matches);
							$matches = str_replace('</dt>', '', $matches);
							$matches = str_replace('<p>', '', $matches);
							$matches = str_replace('</p>', '', $matches);
							$matches = str_replace('<span>', '', $matches);
							$matches = str_replace('</dd>', '', $matches);
							$matches = str_replace('</dl>', '', $matches);
							$matches = str_replace('</div>', '', $matches);
							if(preg_match('<dd class="normal">',$matches[1])){
								$matches = str_replace('<dd class="normal">', '', $matches);
								$bot->replyText($event->getReplyToken(), "阪急箕面線".$matches[1]);
							}else{
								$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
												$matches = str_replace('<span>', '', $matches);
													$bot->replyText($event->getReplyToken(), "阪急箕面線".$matches[1]);
							}
							
 
				 
								
								break;

								case "Takara":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/311/0/");
							preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
							$matches = str_replace('</span>', '', $matches);
							$matches = str_replace('</dt>', '', $matches);
							$matches = str_replace('<p>', '', $matches);
							$matches = str_replace('<span>', '', $matches);
							$matches = str_replace('</p>', '', $matches);
							$matches = str_replace('</dd>', '', $matches);
							$matches = str_replace('</dl>', '', $matches);
							$matches = str_replace('</div>', '', $matches);
							if(preg_match('<dd class="normal">',$matches[1])){
								$matches = str_replace('<dd class="normal">', '', $matches);
								$bot->replyText($event->getReplyToken(), "阪急宝塚本線".$matches[1]);
							}else{
								$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
												$matches = str_replace('<span>', '', $matches);
													$bot->replyText($event->getReplyToken(), "阪急宝塚本線".$matches[1]);
							}
 
 
				 
								break;


							case "Tango":
							$html = file_get_contents("https://transit.yahoo.co.jp/traininfo/detail/375/0/");
							preg_match('/<div id="mdServiceStatus">(.*?)<!--\/#mdServiceStatus-->/is', $html , $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnNormalLarge">', '', $matches);
							$matches = str_replace('</span>', '', $matches);
							$matches = str_replace('</dt>', '', $matches);
							$matches = str_replace('<p>', '', $matches);
							$matches = str_replace('<span>', '', $matches);
							$matches = str_replace('</p>', '', $matches);
							$matches = str_replace('</dd>', '', $matches);
							$matches = str_replace('</dl>', '', $matches);
							$matches = str_replace('</div>', '', $matches);
							if(preg_match('<dd class="normal">',$matches[1])){
								$matches = str_replace('<dd class="normal">', '', $matches);
								$bot->replyText($event->getReplyToken(), "京都丹後".$matches[1]);
							}else{
								$matches = str_replace('<dd class="trouble">', '', $matches);
							$matches = str_replace('<dl>', '', $matches);
							$matches = str_replace('<dt>', '', $matches);
							$matches = str_replace('<span class="icnAlertLarge">', '', $matches);
												$matches = str_replace('<span>', '', $matches);
													$bot->replyText($event->getReplyToken(), "京都丹後".$matches[1]);
							}
							
							break;
								
							



					//���ׂĈႤ�ꍇ
					case "ちびたま":
						DelDB();
						Upload(".");
					default:
						 //$bot->replyText($event->getReplyToken(), $event->getText());
						 $a = DownloadDB();
						 $a .= ",".$Gettext;
						 DelDB();
						 UploadDB($a);
						}
  
					}

					
 ?>
