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
	else {
		// $goodLetters[$alphabet[$i]] = $result;

		for ($j = 0; $j < $result; $j++) {

			$goodLetters[] = $alphabet[$i];
		}
	}

	for ($j = 0; $j < $secretLength; $j++) {
		$grid[$i][$j] = $result;
	}
}

echo "good letters: " . json_encode($goodLetters) . "\n";

arsort($goodLetters);

echo "sorted good letters: " . json_encode($goodLetters) . "\n";

showGrid();

$hwm = 0; // high-water mark

for ($i = 0; $i < $secretLength; $i++) {
	$finalGuess[$i] = "";
}

foreach ($goodLetters as $letter) {
	
	// build a guess

	for ($i = 0; $i < $secretLength; $i++) { // iteration to cover all configs of word
		$baseGuess = $finalGuess;

		for ($j = 0; $j < $secretLength; $j++) { // iteration to construct word

			if ($baseGuess[$j] == "") {

				if ($j == $i) {
					$baseGuess[$j] = $letter;
				}
				else {
					$baseGuess[$j] = $badLetters[0];
				}
			}
		}

		$guess = "";

		foreach ($baseGuess as $val) {
			$guess .= $val;
		}

		echo "the guess is: " . $guess . "\n";

		$obj = sendGuess($guess);

		$result = $obj->numberLettersCorrect;

		if ($result > $hwm) {
			echo "we found a position: " . $i . "\n";
			$finalGuess[$i] = $letter;

			echo "final guess looks like this so far: " . json_encode($finalGuess) . "\n";

			$hwm++;

			break;
		}

	}

	// echo "Continue with script? Type 'y' to continue: " . "\n";
	// $handle = fopen ("php://stdin","r");
	// $line = fgets($handle);
	// if(trim($line) != 'y'){
	//     echo "closing..." . "\n";
	//     exit;
	// }
	// fclose($handle);
	// echo "\n"; 
	// echo "Thank you, continuing...\n";

}

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

	if (!(empty($obj->token))) {
		file_put_contents("token.txt", $obj->token);
	}
	if (!(empty($obj->secretLength))) {
		file_put_contents("secretLength.txt", $obj->secretLength);
	}

	if ($arr[0] == "The last guess was correct") {
	 	exit;
	}

	return $obj;
}