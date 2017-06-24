<?php

class ADMIN
{
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
  }

	public function VERIFY($ID) {
		try {
			$stmt = $this->conn->prepare("SELECT admin FROM users WHERE id='$ID'");
			$stmt->execute();
			$userRow = $stmt->fetch(PDO::FETCH_ASSOC)['admin'];

			if ($userRow == false) {
				exit(http_response_code(403));
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function search_user($admin_id,$input)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM users WHERE id='$input' OR username='$input'");
			if ($stmt->execute()) {
				http_response_code(200);
				$user = $stmt->fetch(PDO::FETCH_ASSOC);
				echo $user['username'];
			} else {
				http_response_code(404);
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function block($admin_id,$user_id)
	{
		try
		{
			$stmt = $this->conn->prepare("UPDATE users SET blocked='true' WHERE id='$user_id'");
			if ($stmt->execute()) {
				http_response_code(200);
				echo "Success";
			} else {
				http_response_code(404);
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function unblock($admin_id,$user_id)
	{
		try
		{
			$stmt = $this->conn->prepare("UPDATE users SET blocked='false' WHERE id='$user_id'");
			if ($stmt->execute()) {
				http_response_code(200);
				echo "Success";
			} else {
				http_response_code(404);
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function get_tickets_assigned($reqUser,$request)
	{
		try
		{
			if ($request == "normal") {
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE admin_id='$reqUser' ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$numItems = count($result);
				$i = 1;
        $idCounter = 0;
				foreach ($result as $item) {
					$reader = $this->conn->prepare("SELECT * FROM reader WHERE user_id='$reqUser' AND chat_id=".$item['id']." AND type='support' ORDER BY chat_id ASC;");
          $reader->execute();
          $reader = $reader->fetch(PDO::FETCH_ASSOC)['counter'];
          $unread = (($reader==0)?"":" (".$reader.")");

					$stmt = $this->conn->prepare("SELECT content FROM support_messages WHERE ticket_id='".$item['id']."' AND type='title';");
					$stmt->execute();
					$content = $stmt->fetch(PDO::FETCH_ASSOC)['content'];
          $idCounter = $idCounter+1;
					if($i++ == $numItems) {
						echo $content.$unread;
					} else {
						echo $content.$unread.",";
					}
          $i = $i++;
				}
			} else {
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE admin_id='$reqUser' ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$numItems = count($result);
				$i = 0;
				foreach ($result as $item) {
					if(++$i === $numItems) {
						echo $item['id'];
					} else {
						echo $item['id'].",";
					}
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function get_tickets_assigned($reqUser,$request)
	{
		try
		{
			if ($request == "normal") {
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE admin_id='$reqUser' ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$numItems = count($result);
				$i = 1;
        $idCounter = 0;
				foreach ($result as $item) {
					$reader = $this->conn->prepare("SELECT * FROM reader WHERE user_id='$reqUser' AND chat_id=".$item['id']." AND type='support' ORDER BY chat_id ASC;");
          $reader->execute();
          $reader = $reader->fetch(PDO::FETCH_ASSOC)['counter'];
          $unread = (($reader==0)?"":" (".$reader.")");

					$stmt = $this->conn->prepare("SELECT content FROM support_messages WHERE ticket_id='".$item['id']."' AND type='title';");
					$stmt->execute();
					$content = $stmt->fetch(PDO::FETCH_ASSOC)['content'];
          $idCounter = $idCounter+1;
					if($i++ == $numItems) {
						echo $content.$unread;
					} else {
						echo $content.$unread.",";
					}
          $i = $i++;
				}
			} else {
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE admin_id='$reqUser' ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$numItems = count($result);
				$i = 0;
				foreach ($result as $item) {
					if(++$i === $numItems) {
						echo $item['id'];
					} else {
						echo $item['id'].",";
					}
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function get_tickets_unassigned($reqUser,$request)
	{
		try
		{
			if ($request == "normal") {
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE admin_id='0' ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$numItems = count($result);
				$i = 1;
        $idCounter = 0;
				foreach ($result as $item) {
					$reader = $this->conn->prepare("SELECT * FROM reader WHERE user_id='$reqUser' AND chat_id=".$item['id']." AND type='support' ORDER BY chat_id ASC;");
          $reader->execute();
          $reader = $reader->fetch(PDO::FETCH_ASSOC)['counter'];
          $unread = (($reader==0)?"":" (".$reader.")");

					$stmt = $this->conn->prepare("SELECT content FROM support_messages WHERE ticket_id='".$item['id']."' AND type='title';");
					$stmt->execute();
					$content = $stmt->fetch(PDO::FETCH_ASSOC)['content'];
          $idCounter = $idCounter+1;
					if($i++ == $numItems) {
						echo $content.$unread;
					} else {
						echo $content.$unread.",";
					}
          $i = $i++;
				}
			} else {
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE admin_id='0' ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$numItems = count($result);
				$i = 0;
				foreach ($result as $item) {
					if(++$i === $numItems) {
						echo $item['id'];
					} else {
						echo $item['id'].",";
					}
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
