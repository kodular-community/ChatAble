<?php

class USER
{
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
  }

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
				$stmt = $this->conn->prepare("SELECT id,username,email,admin,blocked,translate FROM users WHERE email='$user'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
			  print($userRow['id'].",".$userRow['username'].",".$userRow['email'].",".$userRow['admin'].",".$userRow['blocked'].",".$userRow['translate']);
			} elseif ($type == "username") {
				$stmt = $this->conn->prepare("SELECT id,username,email,admin,blocked,translate FROM users WHERE username='$user'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
			  print($userRow['id'].",".$userRow['username'].",".$userRow['email'].",".$userRow['admin'].",".$userRow['blocked'].",".$userRow['translate']);
			} elseif ($type == "id") {
				$stmt = $this->conn->prepare("SELECT id,username,email,admin,blocked,translate FROM users WHERE id='$user'");
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
			  print($userRow['id'].",".$userRow['username'].",".$userRow['email'].",".$userRow['admin'].",".$userRow['blocked'].",".$userRow['translate']);
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

	public function translate($id,$type)
	{
		try
		{
			if ($type == "1") {
				$stmt = $this->conn->prepare("UPDATE `users` SET `translate` = '1' WHERE `users`.`id` = $id;");
				if ($stmt->execute()) { echo "Success"; }
			} elseif ($type == "2") {
				$stmt = $this->conn->prepare("UPDATE `users` SET `translate` = '2' WHERE `users`.`id` = $id;");
				if ($stmt->execute()) { echo "Success"; }
			} elseif ($type == "3") {
				$langs = json_decode(file_get_contents("../utils/translate/gtlanguages.json"), true);
			  $numItems = count($langs);
			  $i = 0;
			  foreach ($langs as $lang) {
			    $i = $i+1;
			    if ($i == $numItems) {
			      echo $lang['language'];
			    } else {
			      echo $lang['language'].",";
			    }
			  }
      }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}

	public function translate_code($id,$code)
	{
		try
		{
			$langs = json_decode(file_get_contents("../utils/translate/gtlanguages.json"), true);
			$stmt = $this->conn->prepare("UPDATE `users` SET `translate` = '".$langs[$code-1]['code']."' WHERE `users`.`id` = $id;");
			if ($stmt->execute()) { echo "Success"; }
		} catch(PDOException $ex) {
			echo $ex->getMessage();
		}
	}
}
