<?php
function API_call($array){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, c('api_domain'));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
		http_build_query(
			$array
		)
	);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}