<?php

$url = "http://jswidler.com/challenge/guess";

$alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

$secretLength = file_get_contents("secretLength.txt");

$finalGuess = [];

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
			$badLetter = $guess[0];
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

		echo "-------------------------------" . "\n";

	}

	for($i = 0; $i < $secretLength; $i++) {
		foreach($res[$i] as $letter => $score) {
			echo "the letter is: " . $letter . "\n";
			// echo "the score is: " . $score["score"] . "\n";

			$score = $score["score"];

			$found = FALSE;

			if ($score == 1 && $found == FALSE) {
				
				for ($j = 0; $j < $secretLength; $j++) {

					$guess = "";

					for ($k = 0; $k < $secretLength; $k++) {
						if ($j == $k) { $guess .= $letter; }
						else { $guess .= $badLetter; }
					}

					echo "the guess is: " . $guess . "\n";

					$obj = sendGuess($guess);

					$arr = explode("...", $obj->message);

					if ($arr[0] == "The last guess was correct") {
				 		exit;
				 	}

					$result = $obj->numberLettersCorrect;

					if ($result == 1) {

						$found = TRUE;

						$finalGuess[$j] = $letter;

						echo "the correct position of " . $letter . " is " . $j . "\n";

						for ($k = 0; $k < $secretLength; $k++) {
							echo "removing " . $letter . " from position " . $k . "\n";
							unset($res[$k][$letter]);
						}
						$res[$j] = [];
						$res[$j][$letter]["score"] = 1;

						showResults();

						$done = TRUE;
						for ($k = 0; $k < $secretLength; $k++) {
							if (sizeof($res[$k]) > 1) { $done = FALSE; break; } 
						}

						if ($done == TRUE) {

							echo "we are done!!!" . "\n";
							$guess = "";
							foreach($res as $list => $letter) {
								foreach ($letter as $key => $value) {
									$guess .= $key;
								}
							}

							echo "the guess is: " . $guess . "\n";

							$obj = sendGuess($guess);

							$arr = explode("...", $obj->message);

							if ($arr[0] == "The last guess was correct") {
								exit;
							}

						}

						echo "-------------------------" . "\n";

						break;
					}
				}
			}
		}
	}

	// We've gone through all the 1's an 0's
	// Establish a baseline to compare new guesses against.

	$baselineGuess = "";

	foreach ($res as $list) {
		$baselineGuess .= key($list);
	}

	echo "the baseline guess is: " . $baselineGuess;

	$obj = sendGuess($baselineGuess);

	$arr = explode("...", $obj->message);

	if ($arr[0] == "The last guess was correct") {
		exit;
	}

	$baseline = $obj->numberLettersCorrect;

	echo "the baseline num correct is: " . $baseline . "\n";

	// Let's find out which rows have the fewest candidates left

	$fewest = $secretLength;
	for ($i = 0; $i < $secretLength; $i++) {
		$len = sizeof($res[$i]);
		if ($len < $fewest && $len > 1) {
			$fewest = $len;
			$row = $i;
		}
	}

	$thisRow = $res[$row];

	$bestResult = 0;

	foreach($thisRow as $key => $value) {
		$guess = "";

		for ($i = 0; $i < $secretLength; $i++) {
			if ($i == $row) {
				$guess .= $key;
			}
			else { $guess .= $badLetter; }
		}

		echo "the guess is: " . $guess . "\n";

		$obj = sendGuess($guess);

		$arr = explode("...", $obj->message);

		if ($arr[0] == "The last guess was correct") {
			exit;
		}

		$result = $obj->numberLettersCorrect;

		if ($result == 1) {
			echo "the correct value is: " . $key . "\n";


		}

		// if ($result > $bestResult) {
		// 	$bestResult = $result;
		// 	$value = $key;
		// }
	}

	// for ($i = 0; $i < $secretLength; $i++) {
	// 	if ($i == $row) {

	// 	}
	// 	$len = sizeof($res[$i]) {
	// 		if ($len < $fewest) {
	// 			$fewest = $len;
	// 			$row = $i;
	// 		}
	// 	}
	// }


	// foreach($res[$row] as $list => $letter) {

	// }

	// $alreadyGuessed = [];

	// $guess = "";

	// foreach ($res as $slot => $letters) {
	// 	// echo "the values are: " . json_encode($letters) . "\n";

	// 	$max = sizeof($letters) - 1;

	// 	$guess .= $letters[rand(0, $max)];

	// }

	// echo "the guess is: " . $guess;



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
