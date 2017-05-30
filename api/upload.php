<?php

/* THANKS TO @TAIFUN FOR THIS SCRIPT (I've edited some things) */

//Start API
session_start();
require_once('../core/functions.php');
$ChatAble = new CHATABLE();



header('Cache-Control: no-cache, must-revalidate');

$data = file_get_contents('php://input');

$filename = $_GET['filename'];
$extension = pathinfo($filename, PATHINFO_EXTENSION);
$new_filename = $_GET['newName'] . "." . $extension;

if ($extension == "jpg" or $extension == "jpeg" or $extension == "png" or $extension == "gif") {
  if (file_put_contents("../media/".$new_filename,$data)) {
    if (filesize("../media/".$new_filename) != 0) {
      http_response_code(200);
      echo $new_filename;
    } else {
      http_response_code(400);
      echo "Empty";
    }
  } else {
    http_response_code(400);
    echo "Failed";
  }
} else {
  http_response_code(400);
  echo "Not image";
}
