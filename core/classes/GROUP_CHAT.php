<?php

class GROUP_CHAT extends CHATABLE
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
				$stmt = $this->conn->prepare("SELECT id,users FROM group_chats ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$i = 1;
        $i2 = 1;
        $numItems = count($result);
        $groupChats = array();
				foreach ($result as $group) {
          $lines = explode(PHP_EOL, $group['users']);
          $users = array();
          foreach ($lines as $line) {
              $users[] = str_getcsv($line);
          }

          foreach ($users[0] as $user) {
            if ($reqUser == $user) {
              $groupChats[] = $group['id'];
            }
          }

          if($i == $numItems) {
    				$numGroups = count($groupChats);
            foreach ($groupChats as $groupId) {
              $reader = $this->conn->prepare("SELECT * FROM reader WHERE user_id='$reqUser' AND chat_id='$groupId' AND type='group' ORDER BY chat_id ASC;");
              $reader->execute();
              $reader = $reader->fetch(PDO::FETCH_ASSOC)['counter'];
              $unread = (($reader==0)?"":" (".$reader.")");

              $stmt = $this->conn->prepare("SELECT title FROM group_chats WHERE id='$groupId';");
              $stmt->execute();
              $title = $stmt->fetch(PDO::FETCH_ASSOC)['title'];
    					if($i2++ == $numGroups) {
    						echo $title.$unread;
    					} else {
    						echo $title.$unread.",";
    					}
              $i2 = $i2++;
            }
          }
          $i = $i+1;
				}
			} else {
        $stmt = $this->conn->prepare("SELECT id,users FROM group_chats ORDER BY timestamp DESC;");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$i = 1;
        $i2 = 1;
        $numItems = count($result);
        $groupChats = array();
				foreach ($result as $group) {
          $lines = explode(PHP_EOL, $group['users']);
          $users = array();
          foreach ($lines as $line) {
              $users[] = str_getcsv($line);
          }

          foreach ($users[0] as $user) {
            if ($reqUser == $user) {
              $groupChats[] = $group['id'];
            }
          }

          if($i == $numItems) {
    				$numGroups = count($groupChats);
            foreach ($groupChats as $groupId) {
              if($i2++ == $numGroups) {
    						echo $groupId;
    					} else {
    						echo $groupId.",";
    					}
              $i2 = $i2++;
            }
          }
          $i = $i+1;
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function create($id,$title,$alias,$password)
	{
		try
		{
      if (!preg_match("/^[a-zA-Z\d]+$/",$alias)) {
        http_response_code(400);
      } else {
        if (!preg_match("/[a-z]/i", $alias)){
            http_response_code(401);
        } else {
          $verAlias = $this->conn->prepare("SELECT * FROM group_chats WHERE alias='$alias';");
          $verAlias->execute();
    			$verAlias = $verAlias->fetchAll(PDO::FETCH_ASSOC);
    			$countAlias = count($verAlias);
          if ($countAlias != 0) {
            http_response_code(402);
          } else {
            $stmt = $this->conn->prepare("INSERT INTO group_chats (title,alias,password,creator,users,timestamp) VALUES ('".ucfirst($title)."','".strtolower($alias)."','{$password}','{$id}','{$id}','".date('U')."');");
            if ($stmt->execute()) {
              $this->update_reader($id,$this->conn->lastInsertId(),0,"new","group");
              http_response_code(200);
              echo "Success";
            } else {
              http_response_code(403);
            }
          }
        }
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function get_messages($groupId,$reqUser)
  {
    try
    {
      $stmt = $this->conn->prepare("SELECT * FROM group_messages WHERE group_id='$groupId' ORDER BY timestamp ASC;");
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			include_once 'READER.php'; $READER = new READER;
      $READER->read($reqUser,$groupId,"group");
      return $result;
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function get_users($groupId,$request)
  {
    try
    {
      $groupUsers = $this->conn->prepare("SELECT users FROM group_chats WHERE id='$groupId';");
      $groupUsers->execute();
      $group_users = $groupUsers->fetch(PDO::FETCH_ASSOC);
      $lines = explode(PHP_EOL, $group_users['users']);
      $users = array();
      foreach ($lines as $line) {
          $users[] = str_getcsv($line);
      }
      foreach ($users[0] as $user) {
        $usernames = $this->conn->prepare("SELECT * FROM users WHERE id='$user';");
        $usernames->execute();
        $usersName[] = $usernames->fetch(PDO::FETCH_ASSOC)[$request];
      }
      return $usersName;
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function get_data($request,$groupId)
  {
    try
    {
      if ($request == "title") {
        $groupName = $this->conn->prepare("SELECT title FROM group_chats WHERE id='$groupId';");
        $groupName->execute();
        return $groupName->fetch(PDO::FETCH_ASSOC)['title'];

      } elseif ($request == "users") {
        return $this->get_users($groupId,"username");

      } elseif ($request == "creator") {
        $groupUsers = $this->conn->prepare("SELECT creator FROM group_chats WHERE id='$groupId';");
        $groupUsers->execute();
        return $groupUsers->fetch(PDO::FETCH_ASSOC)['creator'];

      }
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function post($userId,$groupId,$content,$type,$passwd)
  {
    try
    {
      $insert = $this->conn->prepare("INSERT INTO group_messages (group_id,sender_id,content,type,timestamp) VALUES ('{$groupId}','{$userId}','".AesCtr::encrypt((emoji_unified_to_html($content)), $passwd, 256)."','$type','".date("Y-m-d H:i:s")."');");
      $insert2 = $this->conn->prepare("UPDATE `group_chats` SET timestamp=".date('U')." WHERE id='$groupId';");
      $insert2->execute();
      if ($insert->execute()) {
        http_response_code(200);
        echo "Success";

				include_once 'READER.php'; $READER = new READER;
        $READER->new_msg($userId,$groupId,"group");
      } else {
        http_response_code(401);
      }
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function count($groupId)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM group_messages WHERE group_id='$groupId';");
			$stmt->execute();
			echo $stmt->rowCount();
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function join($ID,$input,$mode)
	{
		try
		{
      if ($mode == "alias") {
        $verAlias = $this->conn->prepare("SELECT * FROM group_chats WHERE alias='$input';");
        $verAlias->execute();
        $verAlias = $verAlias->fetchAll(PDO::FETCH_ASSOC);
        $countAlias = count($verAlias);
      } else {
        $verAlias = $this->conn->prepare("SELECT * FROM group_chats WHERE id='$input';");
        $verAlias->execute();
        $verAlias = $verAlias->fetchAll(PDO::FETCH_ASSOC);
        $countAlias = count($verAlias);
      }

      $users = $this->get_users($verAlias[0]['id'],"id");
      foreach ($users as $user) {
        if ($user == $ID) {
          exit(http_response_code(400));
        }
      }

      if ($countAlias != 1) {
        http_response_code(404);
      } else {
        if (!empty($verAlias[0]['password'])) {
          echo $verAlias[0]['id'];
          http_response_code(403);
        } else {
          $stmt = $this->conn->prepare("UPDATE `group_chats` SET users=CONCAT(users,',".$ID."') WHERE id='".$verAlias[0]['id']."';");
          if ($stmt->execute()) {
						include_once 'READER.php'; $READER = new READER;
            $READER->new_conv($ID,$verAlias[0]['id'],"group");
            http_response_code(200);
            echo "Success";
          } else {
            http_response_code(402);
          }
        }
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function join_passwd($userId,$groupId,$password)
	{
		try
		{
      $group = $this->conn->prepare("SELECT * FROM group_chats WHERE id='$groupId';");
      $group->execute();
      $group = $group->fetch(PDO::FETCH_ASSOC);

      if ($group['password'] != $password) {
        http_response_code(403);
      } else {
        $stmt = $this->conn->prepare("UPDATE `group_chats` SET users=CONCAT(users,',".$userId."') WHERE id='$groupId';");
        if ($stmt->execute()) {
					include_once 'READER.php'; $READER = new READER;
          $READER->new_conv($userId,$groupId,"group");
          http_response_code(200);
          echo "Success";
        } else {
          http_response_code(403);
        }
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
