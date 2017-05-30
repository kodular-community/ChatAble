<?php

// For DEBUG: Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//Start API
session_start();
require_once('functions.php');
$ALERTS = new ALERTS();

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
if (isset($_GET['alerts'])) {
  if (!isset($_GET['id'])) {
    $request = "last";
    $id = 0;
    $result = $ALERTS->get_alerts($request,$id);
    print($result['MAX(id)']);
  } else {
    if (!isset($_GET['request'])) {
      exit("Request a row");
    }
    $id = $_GET['id'];
    $request = $_GET['request'];
    $result = $ALERTS->get_alerts($request,$id);
    print($result[$request]);
  }
}
