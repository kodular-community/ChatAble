<?php

require_once '../core/dbconfig.php';

class VERSIONS
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

	public function version_code()
	{
		try {
			$stmt = $this->conn->prepare("SELECT versionCode FROM `versions` ORDER BY id DESC;");
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
