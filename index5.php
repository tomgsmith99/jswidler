<?php

$url = "http://jswidler.com/challenge/guess";

$alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

$secretLength = file_get_contents("secretLength.txt");

// build results table

for ($i = 0; $i < $secretLength; $i++) {
	for ($j = 0; $j < 26; $j++) {
		$res[$i][$alphabet[$j]]["score"] = 0;
	}
}

// Show initialized result table
showResults();

while (TRUE) {

	// first, the alphabet round

	foreach($alphabet as $letter) {

		$guess = "";

		for ($i = 0; $i < $secretLength; $i++) {
			$guess .= $letter;
		}

	// construct guess



/*
	$foundLowVal = FALSE;

	foreach ($res as $slot => $letters) {
		$lowestScore = 0;

		foreach($letters as $letter => $score) {

			$score = $score["score"];

			if ($score <= $lowestScore) {
				$lowestScore = $score;
				$letterToAdd = $letter;
			}
		}

		$guess .= $letterToAdd;
		// for ($i = 0; $i < $secretLength; $i++) {
		// 	if ($foundLowVal == FALSE) {
		// 		foreach ($letters as $letter => $scoreArr) {

		// 			$score = $scoreArr["score"];

		// 			// echo "the letter is " . $letter . " and the score is: " . $score . "\n";
		// 			if ($score == $i) {
		// 				$guess .= $letter;
		// 				$foundLowVal == TRUE;
		// 				break;
		// 			}
		// 		}				
		// 	}
		// }
	}
*/
	echo "the guess is: " . $guess . "\n";

	// send the guess

	$obj = sendGuess($guess);

	$arr = explode("...", $obj->message);

	if ($arr[0] == "The last guess was correct") {
 		exit;
 	}

	$result = $obj->numberLettersCorrect;

 	// $result = 1;

	if ($result == 0) {
		$badLetters[] = $guess[0];
		for($i = 0; $i < $secretLength; $i++) {
			if (array_key_exists($guess[$i], $res[$i])) {
				unset($res[$i][$guess[$i]]);
			}
		}
	}
	else {
		for($i = 0; $i < $secretLength; $i++) {

			$res[$i][$guess[$i]]["score"] = $result;
		}
	}

	showResults();

	// Ask whether to continue with another iteration
	echo "Continue with script? Type 'y' to continue: " . "\n";
	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if(trim($line) != 'y'){
	    echo "closing..." . "\n";
	    exit;
	}
	fclose($handle);
	echo "\n"; 
	echo "Thank you, continuing...\n";
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

	if (!(empty($obj->token))) {
		file_put_contents("token.txt", $obj->token);
	}
	if (!(empty($obj->secretLength))) {
		file_put_contents("secretLength.txt", $obj->secretLength);
	}

	return $obj;
}

function showResults() {
	global $secretLength;
	global $res;

	$output = "";
	for ($i = 0; $i < $secretLength; $i++) {
		foreach ($res[$i] as $letter => $score) {
			$output .= $letter . "=" . $score["score"] . "|";
		}
		$output .= "\n";
	}

	echo $output;
}
