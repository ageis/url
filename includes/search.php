<?php
error_reporting(0);
require_once './conf.php';
$term = strtolower(urldecode($_GET["term"]));
$parsed = parse_url($term);
if ( empty($parsed['host']) ) {
	$q = $term;
} else {
	$q = $parsed['host'].$parsed['path'];
}
if ( empty($q) ) return;
$items = explode("\n",rtrim(file_get_contents($topsites)));
$items2 = $items;
foreach ($items as &$value) {
    $value = 'https://'.$value;
}
foreach ($items2 as &$value) {
    $value = 'https://www.'.$value;
}
$items = array_merge($items, $items2);
$_count = 0;
$_results = array();
foreach ( $items as $key => $value ) {
	if ( strpos($value,$q) > -1 ) {
		array_push($_results, array("id"=>$key, "label"=>$value, "value" => strip_tags($value)));
		if ( ++$_count > 10 ) break;
	}
}
	echo json_encode($_results);
?>