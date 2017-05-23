<html><head><title>ChatAble Visual Chat</title><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"><link rel="stylesheet" type="text/css" media="all" href="assets/style.css"><link rel="stylesheet" type="text/css" media="all" href="assets/emoji/emoji.css" /><link rel="stylesheet" type="text/css" media="all" href="assets/fontawesome/css/font-awesome.min.css" /><link href="assets/lightbox/css/lightbox.css" rel="stylesheet"></head><body>
<?php
// For DEBUG: Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//Start API
session_start();
require_once('../core/functions.php');
$ChatAble = new CHATABLE();
$Parsedown = new Parsedown();

if (!isset($_GET['reqChat']) or !isset($_GET['reqUser'])) {
  exit("Missing params");
} else {
  $reqUser = $_GET['reqUser'];
  $reqChat = $_GET['reqChat'];
  $passwd = $_GET['passwd'];

  if (!isset($_GET['currentTime'])) {
	  $clientTime = mktime(0,0,0,0,0,0);
  } else {
    $currentTime = html_entity_decode($_GET['currentTime']);
	  $clientTime = mktime(substr($currentTime,11,2),substr($currentTime,14,2),substr($currentTime,17,2),substr($currentTime,5,2),substr($currentTime,8,2),substr($currentTime,0,4));
  }

  $diff = (strtotime(date("Y-m-d H:i:s",$clientTime)) - strtotime(gmdate("Y-m-d H:i:s")));
  $diffSeconds = $diff;
  $diffHours = floor($diffSeconds / 3600);
  $diffSeconds -= $diffHours * 3600;
  $diffMinutes = floor($diffSeconds / 60);
  $diffSeconds -= $diffMinutes * 60;

  if ($_GET['chatType'] == "private") {
    $messages = $ChatAble->get_private_messages($reqChat,$reqUser);
    $convName = $ChatAble->get_private_name($reqChat,$reqUser,"username");
    $guestUser = $ChatAble->get_private_name($reqChat,$reqUser,"id");
?>
    <div class="menu">
      <div class="name"><?php echo $convName; ?></div>
      <div class="members"><?php
        $stmt = $ChatAble->runQuery("SELECT counter FROM reader WHERE user_id='$guestUser' AND chat_id='$reqChat' AND type='private';");
        $stmt->execute();
        $unread = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($unread['counter'] == 0) {
          echo "READ";
        } else {
          echo "NOT READ";
        }
      ?></div>
    </div>

    <ol class="chat">
      <?php
        $currentDay = "";
        if (empty($messages)) {
          echo "<div class='day'>NO MESSAGES</div>";
        } else {
          foreach ($messages as $message) {
            $messageTime = mktime(substr($message['timestamp'],11,2)+$diffHours, substr($message['timestamp'],14,2)+$diffMinutes, substr($message['timestamp'],17,2)+$diffSeconds, substr($message['timestamp'],5,2), substr($message['timestamp'],8,2), substr($message['timestamp'],0,4));
            if ($currentDay != date("d",$messageTime)) {
              if (date("Y-m-d",$messageTime) == date("Y-m-d",$clientTime)) {
                echo "<div class='day'>TODAY</div>";
              } elseif (date("Y-m",$messageTime) == date("Y-m",$clientTime) and date("d",$messageTime) == (date("d",$clientTime)-1)) {
                echo "<div class='day'>YESTERDAY</div>";
              } elseif (date("Y-m",$messageTime) == date("Y-m",$clientTime) and date("d",$messageTime) == (date("d",$clientTime)+1)) {
                echo "<div class='day'>TOMORROW</div>";
              } else {
                echo "<div class='day'>".strtoupper(date("jS \of F",$messageTime))."</div>";
              }
              $currentDay = date("d",$messageTime);
            }

            echo "
            <li class='";if($message['sender_id']==$reqUser){echo"self";}else{echo"other";}echo"'>
              <div class='msg'>";
            if ($message['type'] == "image") {
              echo "<a href=/media/".AesCtr::decrypt($message['content'], $passwd, 256)." data-lightbox='private'><img src=/media/".AesCtr::decrypt($message['content'], $passwd, 256)."></img></a>";
            } else {
              echo "<p>".$Parsedown->text(AesCtr::decrypt($message['content'], $passwd, 256))."</p>";
            }
            echo "<time>".date("H:i",$messageTime)."</time></div></li>";
          }
        }
      ?>
    </ol>
<?php
  } elseif ($_GET['chatType'] == "support") {
    $messages = $ChatAble->get_support_messages($reqChat,$reqUser);
    $ticketName = $ChatAble->get_support_title($reqChat);
?>
    <div class="menu">
      <div class="name"><?php echo $ticketName; ?></div>
      <div class="members"><?php
        $stmt = $ChatAble->runQuery("SELECT admin_id FROM support_tickets WHERE id='$reqChat';");
        $stmt->execute();
        $adminId = $stmt->fetch(PDO::FETCH_ASSOC)['admin_id'];

        if ($adminId == 0) {
          echo "UNASSIGNED";
        } else {
          echo "ASSIGNED TO ";
          $stmt = $ChatAble->runQuery("SELECT username FROM users WHERE id='$adminId';");
          $stmt->execute();
          $adminUsername = ucfirst($stmt->fetch(PDO::FETCH_ASSOC)['username']);
          echo $adminUsername;
        }

        $adminReq = ($reqUser==$adminId) ? true : false ;
      ?></div>
    </div>

    <ol class="chat">
      <?php
        $currentDay = "";
        if (empty($messages)) {
          echo "<div class='day'>NO MESSAGES</div>";
        } else {
          $i = 1;
          foreach ($messages as $message) {
            $messageTime = mktime(substr($message['timestamp'],11,2)+$diffHours, substr($message['timestamp'],14,2)+$diffMinutes, substr($message['timestamp'],17,2)+$diffSeconds, substr($message['timestamp'],5,2), substr($message['timestamp'],8,2), substr($message['timestamp'],0,4));
            if ($currentDay != date("d",$messageTime)) {
              if (date("Y-m-d",$messageTime) == date("Y-m-d",$clientTime)) {
                echo "<div class='day'>TODAY</div>";
              } elseif (date("Y-m",$messageTime) == date("Y-m",$clientTime) and date("d",$messageTime) == (date("d",$clientTime)-1)) {
                echo "<div class='day'>YESTERDAY</div>";
              } elseif (date("Y-m",$messageTime) == date("Y-m",$clientTime) and date("d",$messageTime) == (date("d",$clientTime)+1)) {
                echo "<div class='day'>TOMORROW</div>";
              } else {
                echo "<div class='day'>".strtoupper(date("jS \of F",$messageTime))."</div>";
              }
              $currentDay = date("d",$messageTime);
            }

            echo "
            <li class='";if($message['admin_msg']=="true"){echo"other";}else{echo"self";}echo"'>
              <div class='msg'>";
              if($message['admin_msg']=="true"){echo"<div class='user'>".$adminUsername."<span class='range admin'>Support</span></div>";}
            if ($message['type'] == "image") {
              echo "<a href=/media/".AesCtr::decrypt($message['content'], $passwd, 256)." data-lightbox='support'><img src=/media/".AesCtr::decrypt($message['content'], $passwd, 256)."></img></a>";
            } else {
              if ($i != 1) {
                echo "<p>".$Parsedown->text(AesCtr::decrypt($message['content'], $passwd, 256))."</p>";
              } else {
                echo "<p>".$Parsedown->text($message['content'])."</p>";
              }
            }
            echo "<time>".date("H:i",$messageTime)."</time></div></li>";
            $i = $i+1;
          }
        }
      ?>
    </ol>
<?php
  } elseif ($_GET['chatType'] == "group") {
    $messages = $ChatAble->get_group_messages($reqChat,$reqUser);
    $groupName = $ChatAble->get_group_data("title",$reqChat);
    $groupUsers = $ChatAble->get_group_data("users",$reqChat);
    $groupCreator = $ChatAble->get_group_data("creator",$reqChat);
?>
    <div class="menu">
      <div class="name"><?php echo $groupName; ?></div>
      <div class="members"><?php
        $i = 0;
        $numUsers = count($groupUsers);
        foreach (array_reverse($groupUsers) as $user) {
          $i = $i+1;
          if ($i == $numUsers or $i == 5) {
            echo ucfirst($user);
          } elseif ($i == 6) {
            echo "...";
            break;
          } else {
            echo ucfirst($user).", ";
          }
        }
      ?></div>
    </div>

    <ol class="chat">
      <?php
        $currentDay = "";
        if (empty($messages)) {
          echo "<div class='day'>NO MESSAGES</div>";
        } else {
          foreach ($messages as $message) {
            $username = $ChatAble->runQuery("SELECT username FROM users WHERE id='".$message['sender_id']."'");$username->execute();
            $messageTime = mktime(substr($message['timestamp'],11,2)+$diffHours, substr($message['timestamp'],14,2)+$diffMinutes, substr($message['timestamp'],17,2)+$diffSeconds, substr($message['timestamp'],5,2), substr($message['timestamp'],8,2), substr($message['timestamp'],0,4));
            if ($currentDay != date("d",$messageTime)) {
              if (date("Y-m-d",$messageTime) == date("Y-m-d",$clientTime)) {
                echo "<div class='day'>TODAY</div>";
              } elseif (date("Y-m",$messageTime) == date("Y-m",$clientTime) and date("d",$messageTime) == (date("d",$clientTime)-1)) {
                echo "<div class='day'>YESTERDAY</div>";
              } elseif (date("Y-m",$messageTime) == date("Y-m",$clientTime) and date("d",$messageTime) == (date("d",$clientTime)+1)) {
                echo "<div class='day'>TOMORROW</div>";
              } else {
                echo "<div class='day'>".strtoupper(date("jS \of F",$messageTime))."</div>";
              }
              $currentDay = date("d",$messageTime);
            }

            echo "
            <li class='";if($message['sender_id']==$reqUser){echo"self";}else{echo"other";}echo"'>
              <div class='msg'>";
            if($message['sender_id']!=$reqUser){echo "<div class='user'>".ucfirst($username->fetch(PDO::FETCH_ASSOC)['username']); if($message['sender_id']==$groupCreator){echo"<span class='range admin'>Creator</span>";} echo"</div>";}
            if ($message['type'] == "image") {
              echo "<a href=/media/".AesCtr::decrypt($message['content'], $passwd, 256)." data-lightbox='group'><img src=/media/".AesCtr::decrypt($message['content'], $passwd, 256)."></img></a>";
            } else {
              echo "<p>".$Parsedown->text(AesCtr::decrypt($message['content'], $passwd, 256))."</p>";
            }
            echo "<time>".date("H:i",$messageTime)."</time></div></li>";
          }
        }
      ?>
    </ol>
<?php
  } else {
    echo "Working on this...";
  }
}
?>
  <div id="bottom"></div>
  <script src="assets/lightbox/js/lightbox.js"></script>
  </body>
</html>
