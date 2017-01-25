<?php


$arr = ["g","h","m","n","t","z"];

$alreadyGuessed = [];

$size = sizeof($arr);

$perms = $size * $size;

$guesses[] = $arr;

	$newArr = [];


for ($i = 0; $i < sizeof($arr); $i++) {

	$char = $arr[$i];

	// echo "the char is: " . $char . "\n";

	// echo "the value of i is: " . $i . "\n";

	$newPos = $i - 1;

	if ($newPos === -1 ) { $newPos = sizeof($arr) - 1; }

	$newArr[$newPos] = $char;

}

echo "the new arr is: " . json_encode($newArr) . "\n";

echo "the new arr is: " . print_r($newArr) . "\n";
