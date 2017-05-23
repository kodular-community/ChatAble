<?php

// For DEBUG: Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// DB
require_once('dbconfig.php');

// CONFIG
require_once('config.php');

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

  /*------------------------*/
  //     DB FUNCTIONS     //
  /*------------------------*/
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


  /*------------------------*/
  //     USER FUNCTIONS     //
  /*------------------------*/
	public function login($user,$password,$email)
	{
		try
		{
			if ($email == true) {
				$stmt = $this->conn->prepare("SELECT * FROM users WHERE email='$user'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

				if($stmt->rowCount() == 1) {
					if($userRow['password'] == $password) {
						echo "Success";
						return http_response_code(200);
					} else {
						return http_response_code(401);
					}
				} else {
					return http_response_code(403);
				}
			} else {
				$stmt = $this->conn->prepare("SELECT * FROM users WHERE username='{$user}'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

				if($stmt->rowCount() == 1) {
					if($userRow['password'] == $password) {
						echo "Success";
						return http_response_code(200);
					} else {
						return http_response_code(401);
					}
				} else {
					return http_response_code(403);
				}
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function google_login($email)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM users WHERE email='$email'");
			$stmt->execute();
			$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

			if($stmt->rowCount() == 1) {
				echo "Success";
				return http_response_code(200);
			} else {
				return http_response_code(403);
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function signup($email,$username,$password)
	{
		try
		{
			$test = $this->conn->prepare("SELECT * FROM users WHERE email='{$email}'");
			$test->execute();
			$userRow = $test->fetch(PDO::FETCH_ASSOC);
			if($test->rowCount() == 0) {
				$test2 = $this->conn->prepare("SELECT * FROM users WHERE username='{$username}'");
				$test2->execute();
				$userRow = $test2->fetch(PDO::FETCH_ASSOC);
				if($test2->rowCount() == 0) {
					if (!preg_match("/^[a-zA-Z\d]+$/",$username)) {
			      http_response_code(402);
			    } else {
						if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				      http_response_code(405);
				    } else {
              if (!preg_match("/[a-z]/i", $username)){
                  http_response_code(406);
              } else {
  							$stmt = $this->conn->prepare("INSERT INTO users (email,username,password) VALUES ('{$email}','{$username}','{$password}')");
  							if ($stmt->execute()) {
  								http_response_code(200);
  								echo "Success";
  							} else {
  								http_response_code(401);
  							}
              }
						}
					}
				} else {
					http_response_code(400);
				}
			} else {
				http_response_code(403);
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function edit_account($id,$param,$value)
	{
		try
		{
      if ($param == "username") {
  			$test = $this->conn->prepare("SELECT * FROM users WHERE username='$value'");
  			$test->execute();
  			$testRow = $test->fetch(PDO::FETCH_ASSOC);

        if ($test->rowCount() != 0) {
          echo "Username";
          http_response_code(400);
        } else {
          if (!preg_match("/^[a-zA-Z\d]+$/",$value)) {
            echo "Username";
			      http_response_code(402);
			    } else {
            if (!preg_match("/[a-z]/i", $value)){
              echo "Username";
              http_response_code(401);
            } else {
              $update = $this->conn->prepare("UPDATE `users` SET username='$value' WHERE id='$id';");
        			$update->execute();
              echo "Username";
              http_response_code(200);
            }
          }
        }
      } elseif ($param == "email") {
  			$test = $this->conn->prepare("SELECT * FROM users WHERE email='$value'");
  			$test->execute();
  			$testRow = $test->fetch(PDO::FETCH_ASSOC);

        if ($test->rowCount() != 0) {
          echo "Email";
          http_response_code(400);
        } else {
          if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            echo "Email";
            http_response_code(401);
          } else {
            $update = $this->conn->prepare("UPDATE `users` SET email='$value' WHERE id='$id';");
      			$update->execute();
            echo "Email";
            http_response_code(200);
          }
        }
      } elseif ($param == "password") {
        $update = $this->conn->prepare("UPDATE `users` SET password='$value' WHERE id='$id';");
  			$update->execute();
        echo "Password";
        http_response_code(200);
      } else {
        http_response_code(500);
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function get_data($user,$type)
	{
		try
		{
			if ($type == "email") {
				$stmt = $this->conn->prepare("SELECT id,username,email,admin,blocked FROM users WHERE email='$user'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
			  print($userRow['id'].",".$userRow['username'].",".$userRow['email'].",".$userRow['admin'].",".$userRow['blocked']);

			} elseif ($type == "username") {
				$stmt = $this->conn->prepare("SELECT id,username,email,admin,blocked FROM users WHERE username='$user'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
			  print($userRow['id'].",".$userRow['username'].",".$userRow['email'].",".$userRow['admin'].",".$userRow['blocked']);
			} elseif ($type == "id") {
				$stmt = $this->conn->prepare("SELECT id,username,email,admin,blocked FROM users WHERE id='$user'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
			  print($userRow['id'].",".$userRow['username'].",".$userRow['email'].",".$userRow['admin'].",".$userRow['blocked']);
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function unread_counter($id)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM reader WHERE user_id='$id'");
			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $private_counter = 0;
      $group_counter = 0;
      $support_counter = 0;
      $unknown_counter = 0;

      $i = 0;
      $len = count($items);
      foreach ($items as $item) {
        if ($item['type'] == "private") {
          $private_counter = $private_counter+$item['counter'];
        } elseif ($item['type'] == "group") {
          $group_counter = $group_counter+$item['counter'];
        } elseif ($item['type'] == "support") {
          $support_counter = $support_counter+$item['counter'];
        } else {
          $unknown_counter = $unknown_counter+$item['counter'];
        }

        if ($i == $len - 1) {
          if ($private_counter > 0) {
            echo $private_counter." new private message"; if($private_counter!=1){echo "s";} if($group_counter!=0){echo "\n";}
          }
          if ($group_counter > 0) {
            echo $group_counter." new group message"; if($group_counter!=1){echo "s";} if($support_counter!=0){echo "\n";}
          }
          if ($support_counter > 0) {
            echo $support_counter." new support message"; if($support_counter!=1){echo "s";} if($unknown_counter!=0){echo "\n";}
          }
          if ($unknown_counter > 0) {
            echo $unknown_counter." new unknown message"; if($unknown_counter!=1){echo "s";}
          }
        }

        $i++;
      }

		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}



  /*------------------------*/
  // PRIVATE_CHAT FUNCTION  //
  /*------------------------*/
	public function get_private_chats($reqUser,$request)
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

	public function create_private_chat($reqUser,$guest,$type)
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
            $this->update_reader(0,$convId,0,"new","private");
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

	public function get_private_messages($convId,$reqUser)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT * FROM private_messages WHERE chat_id='$convId' ORDER BY timestamp ASC;");
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $this->update_reader($reqUser,$convId,0,"read","private");
			return $result;
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function get_private_name($convId,$reqUser,$request)
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

			return $result;
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function post_private_message($userId,$convId,$content,$type,$passwd)
	{
		try
		{
		  $insert = $this->conn->prepare("INSERT INTO private_messages (chat_id,sender_id,content,type,timestamp) VALUES ('{$convId}','{$userId}','".AesCtr::encrypt((emoji_unified_to_html($content)), $passwd, 256)."','$type','".date("Y-m-d H:i:s")."');");
      $insert2 = $this->conn->prepare("UPDATE `private_chats` SET timestamp=".date('U')." WHERE id='$convId';");
      $insert2->execute();
			if ($insert->execute()) {
				http_response_code(200);
				echo "Success";

        $this->update_reader($userId,$convId,1,"add","private");
			} else {
				http_response_code(401);
			}
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function count_private_messages($convId)
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



  /*------------------------*/
  //    SUPPORT FUNCTION    //
  /*------------------------*/
  public function get_support_tickets($reqUser,$request)
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

  public function create_support_ticket($reqUser,$title,$content)
	{
		try
		{
			$insertTicket = $this->conn->prepare("INSERT INTO support_tickets (user_id,timestamp) VALUES ('".$reqUser."','".date('U')."');");
      $insertTicket->execute();      $ticketId = $this->conn->lastInsertId();
			$insertTitle = $this->conn->prepare("INSERT INTO support_messages (ticket_id,content,type,timestamp) VALUES ('".$ticketId."','".$title."','title','".date('Y-m-d H:i:s')."');");
			$insertContent = $this->conn->prepare("INSERT INTO support_messages (ticket_id,content,timestamp) VALUES ('".$ticketId."','".$content."','".date('Y-m-d H:i:s')."');");

      if ($insertTitle->execute() and $insertContent->execute()) {
        $this->update_reader($reqUser,$ticketId,0,"new","support");
        http_response_code(200);
        echo "Success";
      } else {
        http_response_code(403);
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

  public function get_support_messages($ticketId,$reqUser)
  {
    try
    {
      $stmt = $this->conn->prepare("SELECT * FROM support_messages WHERE ticket_id='$ticketId' AND type<>'title' ORDER BY timestamp ASC;");
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $this->update_reader($reqUser,$ticketId,0,"read","support");
      return $result;
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function get_support_title($ticketId)
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

  public function post_support_message($userId,$ticketId,$content,$type,$passwd)
  {
    try
    {
      $insert = $this->conn->prepare("INSERT INTO support_messages (ticket_id,content,type,timestamp) VALUES ('{$ticketId}','".AesCtr::encrypt((emoji_unified_to_html($content)), $passwd, 256)."','$type','".date("Y-m-d H:i:s")."');");
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

  public function count_support_messages($ticketId)
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



  /*------------------------*/
  //     GROUP FUNCTION     //
  /*------------------------*/
  public function get_group_chats($reqUser,$request)
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

  public function create_group_chat($id,$title,$alias,$password)
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

  public function get_group_messages($groupId,$reqUser)
  {
    try
    {
      $stmt = $this->conn->prepare("SELECT * FROM group_messages WHERE group_id='$groupId' ORDER BY timestamp ASC;");
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $this->update_reader($reqUser,$groupId,0,"read","group");
      return $result;
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function get_group_users($groupId,$request)
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

  public function get_group_data($request,$groupId)
  {
    try
    {
      if ($request == "title") {
        $groupName = $this->conn->prepare("SELECT title FROM group_chats WHERE id='$groupId';");
        $groupName->execute();
        return $groupName->fetch(PDO::FETCH_ASSOC)['title'];

      } elseif ($request == "users") {
        return $this->get_group_users($groupId,"username");

      } elseif ($request == "creator") {
        $groupUsers = $this->conn->prepare("SELECT creator FROM group_chats WHERE id='$groupId';");
        $groupUsers->execute();
        return $groupUsers->fetch(PDO::FETCH_ASSOC)['creator'];

      }
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function post_group_message($userId,$groupId,$content,$type,$passwd)
  {
    try
    {
      $insert = $this->conn->prepare("INSERT INTO group_messages (group_id,sender_id,content,type,timestamp) VALUES ('{$groupId}','{$userId}','".AesCtr::encrypt((emoji_unified_to_html($content)), $passwd, 256)."','$type','".date("Y-m-d H:i:s")."');");
      $insert2 = $this->conn->prepare("UPDATE `group_chats` SET timestamp=".date('U')." WHERE id='$groupId';");
      $insert2->execute();
      if ($insert->execute()) {
        http_response_code(200);
        echo "Success";

        $this->update_reader($userId,$groupId,1,"add","group");
      } else {
        http_response_code(401);
      }
    } catch(PDOException $ex) {
      echo $ex->getMessage();
    }
  }

  public function count_group_messages($groupId)
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

  public function join_group($ID,$input,$mode)
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

      $users = $this->get_group_users($verAlias[0]['id'],"id");
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
            $this->update_reader($ID,$verAlias[0]['id'],0,"new","group");
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

  public function join_group_password($userId,$groupId,$password)
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
          $this->update_reader($userId,$groupId,0,"new","group");
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



  /*------------------------*/
  //    READER FUNCTION     //
  /*------------------------*/
  public function update_reader($reqUserId,$convId,$newMessages,$mode,$type) {
    if ($mode == "new") {
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

    } elseif ($mode == "add") {
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
        $insert = $this->conn->prepare("UPDATE `reader` SET counter=counter+$newMessages WHERE user_id='$otherId' AND chat_id='$convId' AND type='private';");
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
            $insert = $this->conn->prepare("UPDATE `reader` SET counter=counter+$newMessages WHERE user_id='$user' AND chat_id='$convId' AND type='group';");
      			$insert->execute();
          }
        }
      }

    } elseif ($mode == "read") {
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
}
