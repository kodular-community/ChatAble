<?php

require 'autoload.php';

use Stichoza\GoogleTranslate\TranslateClient;

$tr = new TranslateClient();
$tr->setTarget('es');

echo $tr->translate('Hello World!');
