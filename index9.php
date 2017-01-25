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

// Next, the guessing round

$resGrid = [];

$done = FALSE;

while (!($done)) {

	$alreadyGuessed = [];

	$columns = $secretLength;
	$rows = $secretLength;

	// for each column, find the most likely letter
	for ($i = 0; $i < $columns; $i++) {

		echo "----------------" . "\n";
		echo "starting column " . $i . "..." . "\n";

		for ($j = 0; $j < $rows; $j++) { // $j = index of goodLetter array

			$maxCorrect = 0;

			echo "starting row " . $j . "..." . "\n";

			$guess = "";

			for ($k = 0; $k < $secretLength; $k++) {

				$offset = $k + $j;

				if ($offset >= $secretLength) {
					$offset = $offset - $secretLength;
				}

				$guess .= $goodLetters[$offset];

			}

			echo "the guess is: " . $guess . "\n";

			if (isBad($guess)) {}
			else {

				$obj = sendGuess($guess);

				$result = $obj->numberLettersCorrect;

				if ($result > $maxCorrect) { $maxCorrect = $result; }
			}

			echo "the max correct so far is: " . $maxCorrect . "\n";

		}

		exit;


	}

	$done = TRUE;
}

function isBad($guess) {
	global $resGrid;
	global $secretLength;

	if (empty($resGrid)) { return FALSE; }

	for ($i = 0; $i < $secretLength; $i++) { // columns
		if ($resGrid[$guess[$i]][$i] == 0) { return TRUE; }
	}

	return FALSE;

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