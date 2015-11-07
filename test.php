<?php
include('settings_t.php');
$lat=40.6734;
$lon=16.6038;
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
echo ucfirst($comune);
$url="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20G%20LIKE%20%27%25".$comune."%25%27&key=1ggPvvCCGpF3WlsUEjYX6jaIBVmmu2HdWTvJeZp5X22g";
$csv = array_map('str_getcsv', file($url));
//	$i=1;

$count = 0;
foreach($csv as $data=>$csv1){
	 $count = $count+1;
}
//var_dump($csv);

for ($i=1;$i<$count;$i++){
	$homepage .="\n";
	$homepage .="\nDescrizione: ".$csv[$i][4]."\n";
	$homepage .="\nComune: ".$csv[$i][5]."\n";
	$homepage .="\nTitolo Resp: ".$csv[$i][13]."\n";
	$homepage .="\nNome Resp: ".$csv[$i][6]."\n";
	$homepage .="\nCognome Resp: ".$csv[$i][7]."\n";
  $homepage .="\nIndirizzo: ".$csv[$i][12]."\n";
	$homepage .="\nPec: ".$csv[$i][18]."\n";
		if ($csv[$i][20] != NULL)	$homepage .="\nEmail: ".$csv[$i][20]."\n";
	if ($csv[$i][28] != NULL)		$homepage .="\nPec: ".$csv[$i][28]."\n";
		if ($csv[$i][29] != NULL)		$homepage .="\nFacebook: ".$csv[$i][29]."\n";
		if ($csv[$i][30] != NULL)			$homepage .="\nTwitter: ".$csv[$i][30]."\n";
		if ($csv[$i][31] != NULL)				$homepage .="\nGoogle+: ".$csv[$i][31]."\n";
		if ($csv[$i][32] != NULL)				$homepage .="\nsei già della squadra dei".$csv[$i][31]."\n";


	//$homepage .="Indirizzo: ".$csv[$i][1]."\n";
	if ($csv[$i][13] != NULL) $homepage .="Website: ".$csv[$i][13]."\n";

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
	 $homepage  .="Per visualizzarlo su mappa:".$shortLink['id']."\n";

   echo $homepage;
}
?>
