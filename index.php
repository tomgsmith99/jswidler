<?php

$url = "http://jswidler.com/challenge/guess";

$alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

$secretLength = file_get_contents("secretLength.txt");

// First, get the alphabet counts

foreach ($alphabet as $letter) {

	$guess = "";

	for ($j = 1; $j <= $secretLength; $j++) {
		$guess .= $letter;
	}

	echo "the guess is: " . $guess . "\n";

	$obj = sendGuess($guess);

	$c = $obj->numberLettersCorrect;

	$count[$letter] = $c;

	$totalCount = $totalCount + $c;

	echo "the total count is: " . $totalCount . "\n";

	echo "-----------------------------" . "\n";

	if ($totalCount == $secretLength) { break; }

}

foreach($count as $letter => $freq) {
	for ($i = 1; $i <= $freq; $i++) {
		$letters[] = $letter;
	} 
}

echo "the letters array is: " . json_encode($letters) . "\n";

$guesses = [];
$badGuesses = [];
$alreadyGuessedList = [];

// pc_permute($letters);

$bestGuess;

$firstGuess = TRUE;

$loop = 1000;

$scope = sizeof($letters) 

for ($i = 0; $i <= $loop; $i++) {

	echo "we are on attempt " . $i . "\n";

	// $arr = $guesses[$i];

	if ($firstGuess) {

		$firstGuess = FALSE;

		// $guess = join('', $arr);

		$guess = getGuess();

		echo "a guess is: " . $guess . "\n";

		$obj = sendGuess($guess);

		$lettersCorrect = $obj->numberLettersCorrect;

		if ($lettersCorrect == 0) {
			$badGuess[] = $guess;
		}

		$bestGuess = $guess;

		$alreadyGuessedList[] = $guess;
	}
	else {

		$guess = join("", $arr);

		echo "a potential guess is: " . $guess . "\n";

		if (hasAchance($arr)) {

			echo "this guess has a chance: " . $guess . "\n";

			$obj = sendGuess($guess);

			$lettersCorrect = $obj->numberLettersCorrect;

			if ($lettersCorrect == 0) {
				$badGuess = $guess;
				echo "found a bad guess!" . "\n";
				echo "the bad guess is: " . $badGuess . "\n";
			}

			$bestGuess = $guess;
		}
	}

	$arr = explode("...", $obj->message);

	if ($arr[0] == "The last guess was correct") {
		exit;
	}
	else {
		echo "----------------------------" . "\n";
	}

}

function getGuess() {
	global $letters;
	global $alreadyGuessedList;

	$temp = $letters;

	$guess = "";

	$alreadyGuessed = FALSE;

	while (!(in_array($guess, $alreadyGuessed))) {
		for ($i = sizeof($temp); $i >= 0; $i--) {
			$index = rand(0, $i);
			$guess .= $temp[$index];
			unset($temp[$index]);
			$temp = array_values($temp);
		}

	}

	return $guess;
}

function hasAchance($guess) {
	global $bestGuess;
	global $badGuess; // str
	global $lettersCorrect;
	global $secretLength;

	$match = 0;

	for ($i = 0; $i < sizeof($guess); $i++) {

		if (empty($badGuess)) {}
		else {
			if ($guess[$i] == $badGuess[$i]) {
				return FALSE;
			}
		}

		if ($guess[$i] == $bestGuess[$i]) {
			$match++;
		}
	}

	if ($match >= $lettersCorrect) { return TRUE; }
	else { return FALSE; }

	// if ($match == $lettersCorrect && $lettersCorrect == $secretLength -1) { return TRUE; }
	// else if ($match <= ($secretLength - $lettersCorrect)) { return TRUE; }
	// else { return FALSE; }

}

function pc_permute($items, $perms = array()) {
	global $guesses;

    if (empty($items)) { 
        // echo join(' ', $perms) . "<br />";
        // echo json_encode($perms) . "\n";
        $guesses[] = $perms;
    } else {
        for ($i = count($items) - 1; $i >= 0; --$i) {
             $newitems = $items;
             $newperms = $perms;
             list($foo) = array_splice($newitems, $i, 1);
             array_unshift($newperms, $foo);
             pc_permute($newitems, $newperms);
         }
    }
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

/*

	$guess = getGuess($secretLength);

	echo "the guess is: " . $guess  . "\n";

	$postFields = "guess=" . $guess . "&token=" . $token;

	curl_setopt_array($curl, array(
		CURLOPT_POST => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_URL => $url,
		CURLOPT_POSTFIELDS => $postFields
	));

	$json = curl_exec($curl);

	$obj = json_decode($json);

	file_put_contents("token.txt", $obj->token);
	file_put_contents("secretLength.txt", $obj->secretLength);

	$guessesLeft = $obj->guessesLeft;

	$numberLettersCorrect = $obj->numberLettersCorrect;

	echo "the response is: " . $json . "\n";
*/

// }


// function getGuess($length) {
// 	global $alphabet;
// 	$guess = "";
// 	$max = sizeof($alphabet) - 1;
// 	for ($i=1; $i<=$length; $i++) {
// 		$guess .= $alphabet[rand (0, $max)]; 
// 	}
// 	return $guess;
// }
