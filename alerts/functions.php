<?php

require_once '../core/dbconfig.php';

class ALERTS
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

	public function get_alerts($request,$id)
	{
		try {
			if ($request == "last") {
				$stmt = $this->conn->prepare("SELECT MAX(id) FROM `alerts`;");
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result;
			} else {
				$stmt = $this->conn->prepare("SELECT $request FROM `alerts` WHERE id=$id");
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return $result;
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
