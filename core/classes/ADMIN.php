<?php

class ADMIN
{
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
  }

	public function search_user($admin_id,$input)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT admin FROM users WHERE id='$admin_id'");
			$stmt->execute();
			$userRow = $stmt->fetch(PDO::FETCH_ASSOC)['admin'];

			if ($userRow == false) {
				http_response_code(403);
			} else {
				$stmt = $this->conn->prepare("SELECT * FROM users WHERE id='$input' OR username='$input'");
				if ($stmt->execute()) {
					http_response_code(200);
					$user = $stmt->fetch(PDO::FETCH_ASSOC)[0];
					echo $user['username'];
				} else {
					http_response_code(404);
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function block($admin_id,$user_id)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT admin FROM users WHERE id='$admin_id'");
			$stmt->execute();
			$userRow = $stmt->fetch(PDO::FETCH_ASSOC)['admin'];

			if ($userRow == false) {
				http_response_code(403);
			} else {
				$stmt = $this->conn->prepare("UPDATE users SET blocked='true' WHERE id='$user_id'");
				if ($stmt->execute()) {
					http_response_code(200);
					echo "Success";
				} else {
					http_response_code(404);
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function unblock($admin_id,$user_id)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT admin FROM users WHERE id='$admin_id'");
			$stmt->execute();
			$userRow = $stmt->fetch(PDO::FETCH_ASSOC)['admin'];

			if ($userRow == false) {
				http_response_code(403);
			} else {
				$stmt = $this->conn->prepare("UPDATE users SET blocked='false' WHERE id='$user_id'");
				if ($stmt->execute()) {
					http_response_code(200);
					echo "Success";
				} else {
					http_response_code(404);
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
