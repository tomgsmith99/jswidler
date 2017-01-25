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

	// construct guess

	$guess = "";

	$foundLowVal = FALSE;

	foreach ($res as $slot => $letters) {
		for ($i = 0; $i < $secretLength; $i++) {
			if ($foundLowVal == FALSE) {
				foreach ($letters as $letter => $scoreArr) {

					$score = $scoreArr["score"];

					echo "the letter is " . $letter . " and the score is: " . $score . "\n";
					if ($score == $i) {
						$guess .= $letter["value"];
						$foundLowVal == TRUE;
						break;
					}
				}				
			}
		}
	}

	echo "the guess is: " . $guess . "\n";

	// send the guess

	// $obj = sendGuess($guess);

	// $arr = explode("...", $obj->message);

	// if ($arr[0] == "The last guess was correct") {
 // 		exit;
 // 	}

	// $result = $obj->numberLettersCorrect;

 	$result = 1;

	if ($result == 0) {
		for($i = 0; $i < $secretLength; $i++) {
			if (array_key_exists($guess[$i], $res[$i])) {
				unset($res[$i][$guess[$i]]);
				// $res[$i] = array_values($res[$i]);
			}
			// for ($j = 0; $j < sizeof($res[$i]); $j++) {
			// 	if (array_key_exists("value", $res[$i][$j])) {
			// 		if ($res[$i][$j]["value"] == $guess[$i]) {
			// 			unset($res[$i][$j]);
			// 			$res[$i] = array_values($res[$i]);
			// 			break;
			// 		}
			// 	}

			// }
		}
			// if (array_key_exists($res[$i], ))
		// 	if (in_array($guess[$i], $res[$i])) {

		// 		$res[$i] = array_diff($res[$i], [$guess[$i]]);

		// 		$res[$i] = array_values($res[$i]);

		// 	}
		// }
	}
	// else if ($result == 1) {}
	else {
		for($i = 0; $i < $secretLength; $i++) {

			$res[$i][$guess[$i]]["score"] = $result;
		// 	foreach($res[$i] as $key => $value) {
		// 		if ($value["value"] == $guess[$i]) {
		// 			$value["score"] = $result;
		// 		}
		// 	}
		}
		// for($i = 0; $i < $result; $i++) {
		// 	for ($j = 0; $j < $secretLength; $j++) {
		// 		$res[$j][] = $guess[$j];

		// 		// echo "the new array is: " . json_encode($res[$j]) . "\n";
		// 	}
		// }
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

	// echo "the token is: " . $token;

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

	// first, find the size of the largest array
	// $maxSize = 0;
	// for ($i = 0; $i < $secretLength; $i++) {
	// 	if (sizeof($res[$i]) > $maxSize) { $maxSize = sizeof($res[$i]); }
	// }

	$output = "";
	for ($i = 0; $i < $secretLength; $i++) {
		foreach ($res[$i] as $letter => $score) {
			$output .= $letter . "=" . $score["score"] . "|";
		}
		$output .= "\n";
	}


	echo $output;

	// echo json_encode($res) . "\n";
}
