<?php
/**
* Telegram Bot example for Italian Museums of DBUnico Mibact Lic. CC-BY
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	//$data=new getdata();
	// Instances the class

	/* If you need to manually take some parameters
	*  $result = $telegram->getData();
	*  $text = $result["message"] ["text"];
	*  $chat_id = $result["message"] ["chat"]["id"];
	*/


	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	if ($text == "/start" || $text == "Informazioni") {
		$reply = "Benvenuto. Per ricercare un'IPA, clicca sulla graffetta (📎) e poi 'posizione' oppure digita il nome del Comune. Verrà interrogato il DataBase IPA realizzato da Riccardo Maria Grosso utilizzabile con licenza CC-BY e verranno elencati fino a max 20 IPA. In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale, è stato realizzato da @piersoft e il codice sorgente per libero riuso. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";
		$this->create_keyboard_temp($telegram,$chat_id);


	}elseif ($text == "Città") {
		$reply = "Digita direttamente il nome del Comune.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";

		}
		elseif ($text == "Entitity") {
			$reply = "Se già conosci l'Entity da cercare, scrivi ?nome entity, ad esempio: ?B22_MERCATO COMUNALE";
			$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			$log=$today. ";new chat started;" .$chat_id. "\n";

			}


		//gestione segnalazioni georiferite
		elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;

		}
//elseif($text !=null)

		elseif(strpos($text,'/') === false){
			$url="";
			$location="";
			$count = 0;
			$top=20;
			$iter=0;
			if (strpos($text,'?') !== false) {
				$text=str_replace("?","",$text);
				$location="Sto cercando le ontologie censite dall'IPA.gov.it con entity: ".$text." per max 20 IPA";
			//	$url="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20AJ%20LIKE%20%27%25".ucfirst($text)."%25%27&key=1ggPvvCCGpF3WlsUEjYX6jaIBVmmu2HdWTvJeZp5X22g";
				$text=str_replace(" ","%20",$text);
				$url="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20AJ%20LIKE%20%27%25".ucfirst($text)."%25%27&key=1ggPvvCCGpF3WlsUEjYX6jaIBVmmu2HdWTvJeZp5X22g";
				$iter=1;
		//	if ($count > 19) $top=$count;
		}else{
			$location="Sto cercando le ontologie censite dall'IPA.gov.it del Comune di: ".$text;
			$url="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20G%20LIKE%20%27%25".ucfirst($text)."%25%27&key=1ggPvvCCGpF3WlsUEjYX6jaIBVmmu2HdWTvJeZp5X22g";
			$top=$count;
			$iter=0;
		}
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			sleep (2);
		//	$url="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20G%20LIKE%20%27%25".ucfirst($text)."%25%27&key=1ggPvvCCGpF3WlsUEjYX6jaIBVmmu2HdWTvJeZp5X22g";
			$csv = array_map('str_getcsv', file($url));
			//	$i=1;

			foreach($csv as $data=>$csv1){
				 $count = $count+1;
			}

			//var_dump($csv);
		//	if ($iter==1) $count=21;
			for ($i=1;$i<$count;$i++){
				$homepage .="\n";
				$homepage .="\nDescrizione: ".$csv[$i][5];
				$homepage .="\nComune: ".$csv[$i][6];
				$homepage .="\nTitolo Resp: ".$csv[$i][14];
				$homepage .="\nNome Resp: ".$csv[$i][7];
				$homepage .="\nCognome Resp: ".$csv[$i][8];
				$homepage .="\nIndirizzo: ".$csv[$i][13];
				if ($csv[$i][22] != NULL || $csv[$i][22] !=null)	$homepage .="\nEmail: ".$csv[$i][22];
				if ($csv[$i][23] != NULL || $csv[$i][23] !=null)	$homepage .="\nTipo email: ".$csv[$i][23];
				if ($csv[$i][30] != NULL) $homepage .="\nFacebook: ".$csv[$i][30];
				if ($csv[$i][31] != NULL)	$homepage .="\nTwitter: ".$csv[$i][31];
				if ($csv[$i][32] != NULL)	$homepage .="\nGoogle+: ".$csv[$i][32];
				if ($csv[$i][33] != NULL)	$homepage .="\nYouTube: ".$csv[$i][33];
				if ($csv[$i][12] != NULL) $homepage .="\nWebsite: ".$csv[$i][12];
				if ($csv[$i][12] != NULL) $homepage .="\nEntitles: ".$csv[$i][35];
		if ($csv[$i][1] != NULL){
				$csv[$i][1]=str_replace(",",".",$csv[$i][1]);
				$csv[$i][2]=str_replace(",",".",$csv[$i][2]);

				$longUrl = "http://www.openstreetmap.org/?mlat=".$csv[$i][1]."&mlon=".$csv[$i][2]."#map=19/".$csv[$i][1]."/".$csv[$i][2];

				$apiKey = API;

				 $postData = array('longUrl' => $longUrl, 'key' => $apiKey);
				 $jsonData = json_encode($postData);

				 $curlObj = curl_init();

				 curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
				 curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
				 curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
				 curl_setopt($curlObj, CURLOPT_HEADER, 0);
				 curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
				 curl_setopt($curlObj, CURLOPT_POST, 1);
				 curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

				 $response = curl_exec($curlObj);

				 // Change the response json string to object
				 $json = json_decode($response);

				 curl_close($curlObj);
				 //  $reply="Puoi visualizzarlo su :\n".$json->id;
				 $shortLink = get_object_vars($json);
				 //return $json->id;
				 $homepage  .="\nPer visualizzarlo su mappa: ".$shortLink['id']."\n";
			 }
				 $homepage  .="__________";
				}
			//	echo $alert;

				$chunks = str_split($homepage, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
				$forcehide=$telegram->buildForceReply(true);
				$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

				}


		//		$content = array('chat_id' => $chat_id, 'text' => "Digita un Comune oppure invia la tua posizione tramite la graffetta (📎)");
			//		$telegram->sendMessage($content);
					//aggiorna tastiera
					$this->create_keyboard_temp($telegram,$chat_id);

		}

}


// Crea la tastiera
function create_keyboard($telegram, $chat_id)
 {
	 $forcehide=$telegram->buildKeyBoardHide(true);
	 $content = array('chat_id' => $chat_id, 'text' => "Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo specifico", 'reply_markup' =>$forcehide);
	 $telegram->sendMessage($content);

 }

 function create_keyboard_temp($telegram, $chat_id)
	{
			$option = array(["Città","Entitity"],["Informazioni"]);
			$keyb = $telegram->buildKeyBoard($option, $onetime=false);
			$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Digita un Comune, un'Entity oppure invia la tua posizione tramite la graffetta (📎)]");
			$telegram->sendMessage($content);
	}


function location_manager($telegram,$user_id,$chat_id,$location)
	{

			$lon=$location["longitude"];
			$lat=$location["latitude"];
			$response=$telegram->getData();
			$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
			$json_string = file_get_contents($reply);
			$parsed_json = json_decode($json_string);
			//var_dump($parsed_json);
			$comune="";
			$temp_c1 =$parsed_json->{'display_name'};

			if ($parsed_json->{'address'}->{'town'}) {
				$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'town'};
				$comune .=$parsed_json->{'address'}->{'town'};
			}else 	$comune .=$parsed_json->{'address'}->{'city'};

			$alert="";
			$location="Sto cercando le ontologie censite dall'IPA.gov.it del Comune di: ".$comune;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			sleep (1);
			$url="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20G%20LIKE%20%27%25".ucfirst($comune)."%25%27&key=1ggPvvCCGpF3WlsUEjYX6jaIBVmmu2HdWTvJeZp5X22g";
			$csv = array_map('str_getcsv', file($url));
			//	$i=1;

			$count = 0;
			foreach($csv as $data=>$csv1){
				 $count = $count+1;
			}
			//var_dump($csv);

			for ($i=1;$i<$count;$i++){
				$homepage .="\n";
				$homepage .="\nDescrizione: ".$csv[$i][5];
				$homepage .="\nComune: ".$csv[$i][6];
				$homepage .="\nTitolo Resp: ".$csv[$i][14];
				$homepage .="\nNome Resp: ".$csv[$i][7];
				$homepage .="\nCognome Resp: ".$csv[$i][8];
				$homepage .="\nIndirizzo: ".$csv[$i][13];
				if ($csv[$i][22] != NULL || $csv[$i][22] !=null)	$homepage .="\nEmail: ".$csv[$i][22];
				if ($csv[$i][23] != NULL || $csv[$i][23] !=null)	$homepage .="\nTipo email: ".$csv[$i][23];
				if ($csv[$i][30] != NULL) $homepage .="\nFacebook: ".$csv[$i][30];
				if ($csv[$i][31] != NULL)	$homepage .="\nTwitter: ".$csv[$i][31];
				if ($csv[$i][32] != NULL)	$homepage .="\nGoogle+: ".$csv[$i][32];
				if ($csv[$i][33] != NULL)	$homepage .="\nYouTube: ".$csv[$i][33];
				if ($csv[$i][12] != NULL) $homepage .="\nWebsite: ".$csv[$i][12];
				if ($csv[$i][12] != NULL) $homepage .="\nEntitles: ".$csv[$i][35];
$csv[$i][1]=str_replace(",",".",$csv[$i][1]);
$csv[$i][2]=str_replace(",",".",$csv[$i][2]);

				$longUrl = "http://www.openstreetmap.org/?mlat=".$csv[$i][1]."&mlon=".$csv[$i][2]."#map=19/".$csv[$i][1]."/".$csv[$i][2];

				$apiKey = API;

				 $postData = array('longUrl' => $longUrl, 'key' => $apiKey);
				 $jsonData = json_encode($postData);

				 $curlObj = curl_init();

				 curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
				 curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
				 curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
				 curl_setopt($curlObj, CURLOPT_HEADER, 0);
				 curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
				 curl_setopt($curlObj, CURLOPT_POST, 1);
				 curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

				 $response = curl_exec($curlObj);

				 // Change the response json string to object
				 $json = json_decode($response);

				 curl_close($curlObj);
				 //  $reply="Puoi visualizzarlo su :\n".$json->id;
				 $shortLink = get_object_vars($json);
				 //return $json->id;
				 $homepage  .="\nPer visualizzarlo su mappa: ".$shortLink['id']."\n";
				 $homepage  .="__________";
				}
			//	echo $alert;

				$chunks = str_split($homepage, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
				$forcehide=$telegram->buildForceReply(true);
				$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

				}


			//	$content = array('chat_id' => $chat_id, 'text' => "Digita un Comune oppure invia la tua posizione tramite la graffetta (📎)");
			//		$telegram->sendMessage($content);

		}


}

?>
