<?php

$url = "http://jswidler.com/challenge/guess";

$alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

$secretLength = file_get_contents("secretLength.txt");

// build results table

for ($i = 0; $i < $secretLength; $i++) {
	for ($j = 0; $j < 26; $j++) {
		$res[$i][$j] = $alphabet[$j];
	}
}

while (TRUE) {

	// Show result table
	// first, find the size of the largest array
	$maxSize = 0;
	for ($i = 0; $i < $secretLength; $i++) {
		if (sizeof($res[$i]) > $maxSize) { $maxSize = sizeof($res[$i]); }
	}

	$output = "";
	for ($i = 0; $i < $secretLength; $i++) {
		for ($j = 0; $j < sizeof($res[$i]); $j++) {
			if (array_key_exists($j, $res[$i])) {
				$output .= $res[$i][$j] . "|";
			}
			else { $output .= "X|"; }
		}
		$output .= "\n";
	}

	echo $output;

	// construct guess

	$guess = "";

	foreach ($res as $slot => $letters) {
		// echo "the values are: " . json_encode($letters) . "\n";

		$max = sizeof($letters) - 1;

		$guess .= $letters[rand(0, $max)];

	}

	echo "the guess is: " . $guess . "\n";

	// send the guess

	$result = 2;

	if ($result == 0) {
		for($i = 0; $i < $secretLength; $i++) {
			$elim[$i][$guess[$i]] = FALSE;

			if (in_array($guess[$i], $res[$i])) {
				echo "the bad value is in the array." . "\n";

				$res[$i] = array_diff($res[$i], [$guess[$i]]);

				$res[$i] = array_values($res[$i]);

				// echo "the new array is: " . json_encode($res[$i]) . "\n";
			}
		}
	}
	else if ($result == 1) {}
	else {
		for($i = 0; $i < $result - 1; $i++) {
			for ($j = 0; $j < $secretLength; $j++) {
				$res[$j][] = $guess[$j];

				// echo "the new array is: " . json_encode($res[$j]) . "\n";
			}
		}
	}

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