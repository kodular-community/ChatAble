<?php

// For DEBUG: Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Config
require_once('config.php');
require_once('dbconfig.php');

// Libraries
require_once("libraries/emoji.php");
require_once('libraries/aes.php');
require_once("libraries/markdown.php");

// Security Methods
if (!isset($_GET['key'])) {
  http_response_code(403);
  exit("No Key");
} else {
  if ($_GET['key'] != KEY) {
    http_response_code(403);
    exit("Incorrect Key");
  }
}



class CHATABLE
{
	private $conn;

	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
  }

	public function runQuery($sql)
	{
		$stmt = $this->conn->prepare($sql);
		return $stmt;
	}

  // Load classes
  function LOAD($method)
  {
    if ($method == "USER") {
      include_once 'classes/USER.php';
      $FUNCTION = new USER;
      return $FUNCTION;
    } elseif ($method == "PRIVATE_CHAT") {
      include_once 'classes/PRIVATE_CHAT.php';
      $FUNCTION = new PRIVATE_CHAT;
      return $FUNCTION;
    } elseif ($method == "GROUP_CHAT") {
      include_once 'classes/GROUP_CHAT.php';
      $FUNCTION = new GROUP_CHAT;
      return $FUNCTION;
    } elseif ($method == "SUPPORT_CHAT") {
      include_once 'classes/SUPPORT_CHAT.php';
      $FUNCTION = new SUPPORT_CHAT;
      return $FUNCTION;
    } elseif ($method == "READER") {
      include_once 'classes/READER.php';
      $FUNCTION = new READER;
      return $FUNCTION;
    } elseif ($method == "ADMIN") {
      include_once 'classes/READER.php';
      $FUNCTION = new ADMIN;
      return $FUNCTION;
    }
  }
}
