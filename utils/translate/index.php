<?php

require 'autoload.php';

use Stichoza\GoogleTranslate\TranslateClient;

$tr = new TranslateClient();
$tr->setTarget($_GET['lang']);

echo $tr->translate('Hello World!');
