<?php

class SUPPORT_CHAT
{
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
  }

  public function get_tickets($reqUser,$request)
	{
		try
		{
			if ($request == "normal") {
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE user_id='$reqUser' ORDER BY timestamp DESC;");
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
				$stmt = $this->conn->prepare("SELECT id FROM support_tickets WHERE user_id='$reqUser' ORDER BY timestamp DESC;");
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

  public function create($reqUser,$title,$content)
	{
		try
		{
			$insertTicket = $this->conn->prepare("INSERT INTO support_tickets (user_id,timestamp) VALUES ('".$reqUser."','".date('U')."');");
      $insertTicket->execute();      $ticketId = $this->conn->lastInsertId();
			$insertTitle = $this->conn->prepare("INSERT INTO support_messages (ticket_id,content,type,timestamp) VALUES ('".$ticketId."','".$title."','title','".date('Y-m-d H:i:s')."');");
			$insertContent = $this->conn->prepare("INSERT INTO support_messages (ticket_id,content,timestamp) VALUES ('".$ticketId."','".$content."','".date('Y-m-d H:i:s')."');");

      if ($insertTitle->execute() and $insertContent->execute()) {
				include_once 'READER.php'; $READER = new READER;
	        $READER->new_conv($reqUser,$ticketId,"support");
        http_response_code(200);
        echo "Success";
      } else {
        http_response_code(403);
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function get_messages($ticketId,$reqUser)
  {
    try
    {
      $stmt = $this->conn->prepare("SELECT * FROM support_messages WHERE ticket_id='$ticketId' AND type<>'title' ORDER BY timestamp ASC;");
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			include_once 'READER.php'; $READER = new READER;
			$READER->read($reqUser,$ticketId,"support");
      return $result;
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function get_title($ticketId)
  {
    try
    {
      $ticketTitle = $this->conn->prepare("SELECT content FROM support_messages WHERE ticket_id='$ticketId' AND type='title';");
      $ticketTitle->execute();
      return $ticketTitle->fetch(PDO::FETCH_ASSOC)['content'];
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function post($userId,$ticketId,$content,$type,$passwd)
  {
    try
    {
			$verAdmin = $this->conn->prepare("SELECT admin FROM users WHERE id='$ID'");
			$verAdmin->execute();
			if ($verAdmin->fetch(PDO::FETCH_ASSOC)['admin'] == false) {
				$insert = $this->conn->prepare("INSERT INTO support_messages (ticket_id,content,type,timestamp) VALUES ('{$ticketId}','".AesCtr::encrypt((emoji_unified_to_html($content)), $passwd, 256)."','$type','".date("Y-m-d H:i:s")."');");
			} else {
				$insert = $this->conn->prepare("INSERT INTO support_messages (ticket_id,content,type,admin_msg,timestamp) VALUES ('{$ticketId}','".AesCtr::encrypt((emoji_unified_to_html($content)), $passwd, 256)."','$type','true','".date("Y-m-d H:i:s")."');");
			}
      $insert2 = $this->conn->prepare("UPDATE `support_tickets` SET timestamp=".date('U')." WHERE id='$ticketId';");
      $insert2->execute();
      if ($insert->execute()) {
        http_response_code(200);
        echo "Success";

        $this->update_reader($userId,$ticketId,1,"add","support");
      } else {
        http_response_code(401);
      }
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function count($ticketId)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM support_messages WHERE ticket_id='$ticketId' AND type<>'title';");
			$stmt->execute();
			echo $stmt->rowCount();
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
