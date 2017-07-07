<?php

//Start API
session_start();
require_once('../core/functions.php');

if (!$_SERVER['PATH_INFO']) {
  exit("Request a method");
} else {
  $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
  $method = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
  $function = array_shift($request);
}

$ChatAble = new CHATABLE();


// Handle requests
if ($method == "USER") {
  $USER = $ChatAble->LOAD("USER");

  if ($function == "login") {
    if (!isset($_GET['user']) or !isset($_GET['password'])) {
      exit("Missing params");
    }
    $user = $_GET['user'];
    $password = md5($_GET['password']);
    if (strpos($user, '@') != false) {
      $email = true;
    } else {
      $email = false;
    }
    $result = $USER->login($user,$password,$email);

  } elseif ($function == "google_login") {
    if (!isset($_GET['email'])) {
      exit("Missing params");
    }
    $email = $_GET['email'];
    $result = $USER->google_login($email);

  } elseif ($function == "signup") {
    if (!isset($_GET['email']) or !isset($_GET['username']) or !isset($_GET['password'])) {
      exit("Missing params");
    }
    $email = strtolower(html_entity_decode($_GET['email']));
    $username = strtolower(html_entity_decode($_GET['username']));
    $password = md5(html_entity_decode($_GET['password']));
    $result = $USER->signup($email,$username,$password);

  } elseif ($function == "edit_account") {
    if (!isset($_GET['id']) or !isset($_GET['param']) or !isset($_GET['value'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $param = $_GET['param'];
    if ($param == "password") {
      $value = md5($_GET['value']);
    } else {
      $value = strtolower($_GET['value']);
    }
    $result = $USER->edit_account($id,$param,$value);

  } elseif ($function == "get_data") {
    if (!isset($_GET['user']) or  empty($_GET['user'])) {
      exit("Missing email");
    }
    $user = $_GET['user'];
    if (strpos($user, '@') != false) {
      $type = "email";
    } elseif (is_numeric($user)) {
      $type = "id";
    } else {
      $type = "username";
    }
    $result = $USER->get_data($user,$type);

  } elseif ($function == "unread_counter") {
    if (!isset($_GET['id'])) {
      exit("Missing id");
    }
    $id = $_GET['id'];
    $result = $USER->unread_counter($id);

  } elseif ($function == "translate") {
    if (!isset($_GET['id']) or !isset($_GET['type'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $type = $_GET['type'];
    $result = $USER->translate($id,$type);

  } elseif ($function == "translate_code") {
    if (!isset($_GET['id']) or !isset($_GET['code'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $code = $_GET['code'];
    $result = $USER->translate_code($id,$code);

  } else {
    exit("Bad Request");
  }


} elseif ($method == "PRIVATE") {
  $PRIVATE = $ChatAble->LOAD("PRIVATE_CHAT");

  if ($function == "get_chats") {
    if (!isset($_GET['id'])) {
      exit("Missing id");
    }
    $id = $_GET['id'];
    if (isset($_GET['request'])) {
      $request = $_GET['request'];
    } else{
      $request = "normal";
    }
    $result = $PRIVATE->get_chats($id,$request);

  } elseif ($function == "create") {
    if (!isset($_GET['id']) or !isset($_GET['guest'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $guest = $_GET['guest'];
    if (is_numeric($_GET['guest'])) {
      $type = "id";
    } elseif (strpos($guest, '@') != false) {
      $type = "email";
    } else {
      $type = "username";
      $guest = strtolower($guest);
    }
    $result = $PRIVATE->create($id,$guest,$type);

  } elseif ($function == "post") {
    if (!isset($_GET['userId']) or !isset($_GET['convId']) or !isset($_GET['content'])) {
      exit("Missing params");
    }
    $userId = $_GET['userId'];
    $convId = $_GET['convId'];
    $content = html_entity_decode($_GET['content']);
    $passwd = $_GET['passwd'];
    if (isset($_GET['type'])) {
      $type = $_GET['type'];
    } else {
      $type = "text";
    }
    $result = $PRIVATE->post($userId,$convId,$content,$type,$passwd);

  } elseif ($function == "count") {
    if (!isset($_GET['convId'])) {
      exit("Missing params");
    }
    $convId = $_GET['convId'];
    $result = $PRIVATE->count($convId);

  } else {
    exit("Bad Request");
  }


} elseif ($method == "SUPPORT") {
  $SUPPORT = $ChatAble->LOAD("SUPPORT_CHAT");

  if ($function == "get_tickets") {
    if (!isset($_GET['id'])) {
      exit("Missing id");
    }
    $id = $_GET['id'];
    if (isset($_GET['request'])) {
      $request = $_GET['request'];
    } else{
      $request = "normal";
    }
    $result = $SUPPORT->get_tickets($id,$request);

  } elseif ($function == "create") {
    if (!isset($_GET['id']) or !isset($_GET['title']) or !isset($_GET['content'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $title = ucfirst(html_entity_decode($_GET['title']));
    $content = ucfirst(html_entity_decode($_GET['content']));
    $result = $SUPPORT->create($id,$title,$content);

  } elseif ($function == "post_support_message") {
    if (!isset($_GET['userId']) or !isset($_GET['ticketId']) or !isset($_GET['content'])) {
      exit("Missing params");
    }
    $userId = $_GET['userId'];
    $ticketId = $_GET['ticketId'];
    $content = html_entity_decode($_GET['content']);
    $passwd = $_GET['passwd'];
    if (isset($_GET['type'])) {
      $type = $_GET['type'];
    } else {
      $type = "text";
    }
    $result = $SUPPORT->post($userId,$ticketId,$content,$type,$passwd);

  } elseif ($function == "count") {
    if (!isset($_GET['ticketId'])) {
      exit("Missing params");
    }
    $ticketId = $_GET['ticketId'];
    $result = $SUPPORT->count($ticketId);

  } else {
    exit ("Bad Request");
  }


} elseif ($method == "GROUP") {
  $GROUP = $ChatAble->LOAD("GROUP_CHAT");

  if ($function == "get_chats") {
    if (!isset($_GET['id'])) {
      exit("Missing id");
    }
    $id = $_GET['id'];
    if (isset($_GET['request'])) {
      $request = $_GET['request'];
    } else{
      $request = "normal";
    }
    $result = $GROUP->get_chats($id,$request);

  } elseif ($function == "create") {
    if (!isset($_GET['id']) or !isset($_GET['title']) or !isset($_GET['alias']) or !isset($_GET['password'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $title = html_entity_decode($_GET['title']);
    $alias = strtolower($_GET['alias']);
    $password = (empty($_GET['password'])) ? "": md5(html_entity_decode($_GET['password']));
    $result = $GROUP->create($id,$title,$alias,$password);

  } elseif ($function == "post") {
    if (!isset($_GET['userId']) or !isset($_GET['groupId']) or !isset($_GET['content'])) {
      exit("Missing params");
    }
    $userId = $_GET['userId'];
    $groupId = $_GET['groupId'];
    $content = html_entity_decode($_GET['content']);
    $passwd = $_GET['passwd'];
    if (isset($_GET['type'])) {
      $type = $_GET['type'];
    } else {
      $type = "text";
    }
    $result = $GROUP->post($userId,$groupId,$content,$type,$passwd);

  } elseif ($function == "count") {
    if (!isset($_GET['groupId'])) {
      exit("Missing params");
    }
    $groupId = $_GET['groupId'];
    $result = $GROUP->count($groupId);

  } elseif ($function == "join") {
    if (!isset($_GET['id']) or !isset($_GET['input'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $input = $_GET['input'];
    if (is_numeric($input)) {
      $mode = "id";
    } else {
      $mode = "alias";
      $input = strtolower($input);
    }
    $result = $GROUP->join($id,$input,$mode);

  } elseif ($function == "join_password") {
    if (!isset($_GET['id']) or !isset($_GET['groupId']) or !isset($_GET['password'])) {
      exit("Missing params");
    }
    $id = $_GET['id'];
    $groupId = $_GET['groupId'];
    $password = md5(html_entity_decode($_GET['password']));
    $result = $GROUP->join_passwd($id,$groupId,$password);

  } else {
    exit("Bad Request");
  }


} elseif ($method == "ADMIN") {
  $ADMIN = $ChatAble->LOAD("ADMIN");
  if (!isset($_GET['admin_id'])) {
    exit('No admin');
  } else {
    $ADMIN->VERIFY($_GET['admin_id']);
  }

  if ($function == "search_user") {
    if (!isset($_GET['admin_id']) or !isset($_GET['input'])) {
      exit("Missing params");
    }
    $admin_id = $_GET['admin_id'];
    $input = $_GET['input'];
    $result = $ADMIN->search_user($admin_id,$input);

  } elseif ($function == "block") {
    if (!isset($_GET['admin_id']) or !isset($_GET['user_id'])) {
      exit("Missing params");
    }
    $admin_id = $_GET['admin_id'];
    $user_id = $_GET['user_id'];
    $result = $ADMIN->block($admin_id,$user_id);

  } elseif ($function == "unblock") {
    if (!isset($_GET['admin_id']) or !isset($_GET['user_id'])) {
      exit("Missing params");
    }
    $admin_id = $_GET['admin_id'];
    $user_id = $_GET['user_id'];
    $result = $ADMIN->unblock($admin_id,$user_id);

  } elseif ($function == "get_tickets_assigned") {
    if (!isset($_GET['admin_id'])) {
      exit("Missing id");
    }
    $id = $_GET['admin_id'];
    if (isset($_GET['request'])) {
      $request = $_GET['request'];
    } else{
      $request = "normal";
    }
    $result = $ADMIN->get_tickets_assigned($id,$request);

  } elseif ($function == "get_tickets_unassigned") {
    if (!isset($_GET['admin_id'])) {
      exit("Missing id");
    }
    $id = $_GET['admin_id'];
    if (isset($_GET['request'])) {
      $request = $_GET['request'];
    } else{
      $request = "normal";
    }
    $result = $ADMIN->get_tickets_unassigned($id,$request);

  } elseif ($function == "get_ticket_info") {
    if (!isset($_GET['admin_id']) or !isset($_GET['ticket_id'])) {
      exit("Missing params");
    }
    $ticket_id = $_GET['ticket_id'];
    $result = $ADMIN->get_ticket_info($ticket_id);

  } elseif ($function == "assign_ticket") {
    if (!isset($_GET['admin_id']) or !isset($_GET['ticket_id'])) {
      exit("Missing params");
    }
    $id = $_GET['admin_id'];
    $ticket_id = $_GET['ticket_id'];
    $result = $ADMIN->assign_ticket($id,$ticket_id);

  } else {
    exit("Bad Request");
  }


} else {
  exit("Bad request");
}
