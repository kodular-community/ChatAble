<?php

$lANGUAGES = json_decode(file_get_contents("list.json"), true)[0];
$SUPPORTED = array();

$reqLanguages = html_entity_decode($_GET['languages']);
$lines = explode(PHP_EOL, $reqLanguages);
$langArray = array();

foreach ($lines as $line) {
    $langArray[] = str_getcsv($line);
}

foreach ($langArray[0] as $lang) {
  if (array_key_exists($lang,$lANGUAGES)) {
    $SUPPORTED[] = $lANGUAGES[$lang];
  }
}
foreach ($langArray[0] as $lang) {
  if (!array_key_exists($lang,$lANGUAGES)) {
    $SUPPORTED[] = $lang;
  }
}

$numItems = count($SUPPORTED);
$i = 0;
foreach ($SUPPORTED as $LANG) {
  $i = $i+1;
  if ($i == $numItems) {
    echo "\"".$LANG."\"";
  } else {
    echo "\"".$LANG."\"".",";
  }
}
