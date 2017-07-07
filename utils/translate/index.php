<?php

if ($_GET['mode'] == 'demo') {
  $tr = new TranslateClient();
  $tr->setTarget($_GET['lang']);
  echo $tr->translate('Hello World!');
} else {
  $langs = json_decode(file_get_contents("gtlanguages.json"), true);
  $numItems = count($langs);
  $i = 0;
  foreach ($langs as $lang) {
    $i = $i+1;
    if ($i == $numItems) {
      echo $lang['language'];
    } else {
      echo $lang['language']."<br>";
    }
  }
}
