<?php


$arr = '[{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"p":{"score":1}},{"n":{"score":1}},{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"z":{"score":1}},{"q":{"score":1}},{"u":{"score":1}},{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"g":{"score":1}},{"a":{"score":1}},{"b":{"score":2},"e":{"score":2},"k":{"score":2},"r":{"score":2}},{"f":{"score":1}}]';

$newArr = json_decode($arr, TRUE);

echo print_r($newArr);

foreach ($newArr as $list) {
	echo json_encode($list) . "\n";

	echo "the first element is: " . key($list) . "\n";
}