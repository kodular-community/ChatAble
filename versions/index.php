<?php

// For DEBUG: Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//Start API
session_start();
require_once('functions.php');
$VERSIONS = new VERSIONS();

// Security Methods
$key = "ChatAble";
if (!isset($_GET['key'])) {
  http_response_code(403);
  exit("No Key");
} else {
  if ($_GET['key'] != $key) {
    http_response_code(403);
    exit("Incorrect Key");
  }
}


// Function
if (isset($_GET['versions'])) {
  require_once("../core/config.php");
  $result = $VERSIONS->version_code();
  print($result['versionCode']);
}
