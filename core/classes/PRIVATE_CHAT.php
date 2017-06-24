<?php

class PRIVATE_CHAT
{
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
  }

	public function get_chats($reqUser,$request)
	{
		try
		{
			if ($request == "normal") {
				$stmt = $this->conn->prepare("SELECT user_server,user_client FROM private_chats WHERE user_server='$reqUser' OR user_client='$reqUser' ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chatID = $this->conn->prepare("SELECT id FROM private_chats WHERE user_server='$reqUser' OR user_client='$reqUser' ORDER BY timestamp DESC;");
				$chatID->execute();
				$chatID = $chatID->fetchAll(PDO::FETCH_ASSOC);

				$numItems = count($result);
				$i = 1;
        $idCounter = 0;
				foreach ($result as $item) {
					foreach ($item as $user) {
						if ($user != $reqUser) {
              $reader = $this->conn->prepare("SELECT * FROM reader WHERE user_id='$reqUser' AND chat_id=".$chatID[$idCounter]['id']." AND type='private' ORDER BY chat_id ASC;");
              $reader->execute();
              $reader = $reader->fetch(PDO::FETCH_ASSOC)['counter'];
              $unread = (($reader==0)?"":" (".$reader.")");

							$stmt = $this->conn->prepare("SELECT username FROM users WHERE id='$user';");
							$stmt->execute();
							$user = $stmt->fetch(PDO::FETCH_ASSOC)['username'];
              $idCounter = $idCounter+1;
							if($i++ == $numItems) {
								echo $user.$unread;
							} else {
								echo $user.$unread.",";
							}
              $i = $i++;
						}
					}
				}
			} else {
				$stmt = $this->conn->prepare("SELECT id FROM private_chats WHERE user_server='$reqUser' OR user_client='$reqUser' ORDER BY timestamp DESC;");
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

	public function create($reqUser,$guest,$type)
	{
		try
		{
			if ($type == "id") {
				$guestId = $this->conn->prepare("SELECT * FROM users WHERE id='$guest';");
				$guestId->execute();
				$guestId = $guestId->fetch(PDO::FETCH_ASSOC)['id'];
			} elseif ($type == "email") {
				$guestId = $this->conn->prepare("SELECT * FROM users WHERE email='$guest';");
				$guestId->execute();
				$guestId = $guestId->fetch(PDO::FETCH_ASSOC)['id'];
			} else {
				$guestId = $this->conn->prepare("SELECT * FROM users WHERE username='$guest';");
				$guestId->execute();
				$guestId = $guestId->fetch(PDO::FETCH_ASSOC)['id'];
			}

			if ($reqUser == $guestId) {
				exit(http_response_code(500));
			}

			$chatsCount = $this->conn->prepare("SELECT * FROM private_chats WHERE user_server='$reqUser' AND user_client='$guestId' OR user_server='$guestId' AND user_client='$reqUser' ;");
			$chatsCount->execute();
			$counter = $chatsCount->fetchAll(PDO::FETCH_ASSOC);
			$count = count($counter);
			if($count > 0) {
				http_response_code(403);
			} else {
				$verUser = $this->conn->prepare("SELECT * FROM users WHERE id='$guestId';");
				$verUser->execute();
				$verUserResult = $verUser->fetchAll(PDO::FETCH_ASSOC);
				$count2 = count($verUserResult);
				if ($count2 == 1) {
					$stmt = $this->conn->prepare("INSERT INTO private_chats (user_server,user_client, timestamp) VALUES ('{$reqUser}','{$guestId}','".date('U')."')");
					if ($stmt->execute()) {
						http_response_code(200);
						echo "Success";

            $convId = $this->conn->lastInsertId();
						include_once 'READER.php'; $READER = new READER;
            $READER->new_conv(0,$convId,"private");
					} else {
						http_response_code(401);
					}
				} else {
					http_response_code(400);
				}
			}

		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function get_messages($convId,$reqUser)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM private_messages WHERE chat_id='$convId' AND type<>'cleverbot' ORDER BY timestamp ASC;");
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			include_once 'READER.php'; $READER = new READER;
      $READER->read($reqUser,$convId,"private");
			return $result;
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function get_name($convId,$reqUser,$request)
	{
		try
		{
			$convName = $this->conn->prepare("SELECT user_server,user_client FROM private_chats WHERE id='$convId';");
			$convName->execute();
			$convNAME = $convName->fetch(PDO::FETCH_ASSOC);
			foreach ($convNAME as $user) {
				if ($user != $reqUser) {
					$username = $this->conn->prepare("SELECT $request FROM users WHERE id='$user';");
					$username->execute();
					return $username->fetch(PDO::FETCH_ASSOC)[$request];
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function post($userId,$convId,$content,$type,$passwd)
	{
		try
		{
		  $insert = $this->conn->prepare("INSERT INTO private_messages (chat_id,sender_id,content,type,timestamp) VALUES ('{$convId}','{$userId}','".AesCtr::encrypt((emoji_unified_to_html($content)), $passwd, 256)."','$type','".date("Y-m-d H:i:s")."');");
      $insert2 = $this->conn->prepare("UPDATE `private_chats` SET timestamp=".date('U')." WHERE id='$convId';");
			if ($insert->execute() and $insert2->execute()) {
				$cleverbot = $this->conn->prepare("SELECT user_client FROM private_chats WHERE id=$convId;");
				$cleverbot->execute();
				if (CLEVERBOT != "" and $cleverbot->fetch(PDO::FETCH_ASSOC)['user_client'] == 1 and $userId != 1) {
					/*
					$check = $this->conn->prepare("SELECT * FROM private_messages WHERE chat_id='$convId' AND type='cleverbot';");
					$check->execute();
					$csV = $check->fetchAll(PDO::FETCH_ASSOC);
					if($check->rowCount() > 0) {
						$cs = $csV[max(array_keys($csV))]['content'];
					} else {
						*/$cs = "null";/*
					}
					*/
					$base_url = 'https://www.cleverbot.com/getreply';
					if ($cs != "null") {
				    $url = $base_url . "?input=".rawurlencode($content) . "&key=".CLEVERBOT . "&cs=".str_replace('=','',$cs)."&callback=ProcessReply";
					} else {
						$url = $base_url . "?input=".rawurlencode($content) . "&key=".CLEVERBOT;
					}
			    $response = file_get_contents($url);
			    $output = json_decode($response, true);

					$this->post(1,$convId,$output['output'],'text',$passwd);
				} else {
					http_response_code(200);
					echo "Success";
					include_once 'READER.php'; $READER = new READER;
	        $READER->new_msg($userId,$convId,"private");
				}
			} else {
				http_response_code(401);
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function count($convId)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM private_messages WHERE chat_id='$convId';");
			$stmt->execute();
			echo $stmt->rowCount();
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
