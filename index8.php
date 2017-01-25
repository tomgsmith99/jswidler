<?php

$url = "http://jswidler.com/challenge/guess";

$alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

$secretLength = file_get_contents("secretLength.txt");

/***************************************************/
// First, the alphabet round

$foundSoFar = 0;

for ($i = 0; $i < 26; $i++) {

	if ($foundSoFar == $secretLength) { $result = 0; }

	else {
		$guess = "";

		for ($j = 0; $j < $secretLength; $j++) {
			$guess .= $alphabet[$i];
		}

		echo "the guess is: " . $guess . "\n";

		$obj = sendGuess($guess);

		$result = $obj->numberLettersCorrect;

		$foundSoFar += $result;

	}

	if ($result == 0) {
		$badLetters[] = $alphabet[$i];
	}

	for ($j = 0; $j < $secretLength; $j++) {
		$grid[$i][$j] = $result;
	}
}

showGrid();

function showGrid() {
	global $grid;
	global $alphabet;
	global $secretLength;
	global $badLetters;

	for ($i = 0; $i < 26; $i++) {
		if (in_array($alphabet[$i], $badLetters)) {}
		else {
			echo $alphabet[$i] . ": ";
			for ($j = 0; $j < $secretLength; $j++) {
				echo $grid[$i][$j] . "|";
			}
			echo "\n";			
		}
	}
}

function sendGuess($guess) {
	global $url;

	$curl = curl_init();

	$token = file_get_contents("token.txt");

	$postFields = "guess=" . $guess . "&token=" . $token;

	curl_setopt_array($curl, array(
		CURLOPT_POST => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_URL => $url,
		CURLOPT_POSTFIELDS => $postFields
	));

	$json = curl_exec($curl);

	curl_close($curl);

	echo "the result is: " . $json . "\n";

	$obj = json_decode($json);

	$arr = explode("...", $obj->message);

	if ($arr[0] == "The last guess was correct") {
	 	exit;
	}

	if (!(empty($obj->token))) {
		file_put_contents("token.txt", $obj->token);
	}
	if (!(empty($obj->secretLength))) {
		file_put_contents("secretLength.txt", $obj->secretLength);
	}

	return $obj;
}