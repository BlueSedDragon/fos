<?php

function redirect($raw_url) {
	$url = urlencode($raw_url);

	http_response_code(302);
	header("Location: ${raw_url}");
	
	$url = htmlentities($url);
	$word = htmlentities($raw_url);

	echo "Redirecting to <a href='${url}'>${word}</a>\n";
}
