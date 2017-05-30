<?php

class READER extends CHATABLE
{
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
  }

  public function new_conv($reqUserId,$convId,$type) {
    if ($type == "private") {
      $insertS = $this->conn->prepare("INSERT INTO reader (user_id,chat_id,counter,type) VALUES ((SELECT user_server FROM private_chats WHERE id=$convId),$convId,'0','private');");
			$insertS->execute();
      $insertC = $this->conn->prepare("INSERT INTO reader (user_id,chat_id,counter,type) VALUES ((SELECT user_client FROM private_chats WHERE id=$convId),$convId,'0','private');");
			$insertC->execute();
    } elseif ($type == "support") {
      $insert = $this->conn->prepare("INSERT INTO reader (user_id,chat_id,counter,type) VALUES ($reqUserId,$convId,'0','support');");
			$insert->execute();
    } elseif ($type == "group") {
      $insert = $this->conn->prepare("INSERT INTO reader (user_id,chat_id,counter,type) VALUES ($reqUserId,$convId,'0','group');");
			$insert->execute();
    }
  }

  public function new_msg($reqUserId,$convId,$type) {
    if ($type == "private") {
      $convID = $this->conn->prepare("SELECT user_server,user_client FROM private_chats WHERE id='$convId';");
			$convID->execute();
			$convID = $convID->fetch(PDO::FETCH_ASSOC);
			foreach ($convID as $user) {
				if ($user != $reqUserId) {
					$ID = $this->conn->prepare("SELECT id FROM users WHERE id='$user';");
					$ID->execute();
					$otherId = $ID->fetch(PDO::FETCH_ASSOC)['id'];
				}
			}
      $insert = $this->conn->prepare("UPDATE `reader` SET counter=counter+1 WHERE user_id='$otherId' AND chat_id='$convId' AND type='private';");
			$insert->execute();
    } elseif ($type == "group") {
      $groupUsers = $this->conn->prepare("SELECT users FROM group_chats WHERE id='$convId';");
      $groupUsers->execute();
      $group_users = $groupUsers->fetch(PDO::FETCH_ASSOC);
      $lines = explode(PHP_EOL, $group_users['users']);
      $users = array();
      foreach ($lines as $line) {
          $users[] = str_getcsv($line);
      }
      foreach ($users[0] as $user) {
        $userIDs = $this->conn->prepare("SELECT id FROM users WHERE id='$user';");
        $userIDs->execute();
        $USERS[] = $userIDs->fetch(PDO::FETCH_ASSOC)['id'];
      }
      foreach ($USERS as $user) {
        if ($user != $reqUserId) {
          $insert = $this->conn->prepare("UPDATE `reader` SET counter=counter+1 WHERE user_id='$user' AND chat_id='$convId' AND type='group';");
    			$insert->execute();
        }
      }
    }
  }

  public function read($reqUserId,$convId,$type) {
    if ($type == "private") {
      $insert = $this->conn->prepare("UPDATE `reader` SET counter=0 WHERE user_id='$reqUserId' AND chat_id='$convId' AND type='private';");
			$insert->execute();
    } elseif ($type == "support") {
      $insert = $this->conn->prepare("UPDATE `reader` SET counter=0 WHERE user_id='$reqUserId' AND chat_id='$convId' AND type='support';");
			$insert->execute();
    } elseif ($type == "group") {
      $insert = $this->conn->prepare("UPDATE `reader` SET counter=0 WHERE user_id='$reqUserId' AND chat_id='$convId' AND type='group';");
			$insert->execute();
    }
  }
}
