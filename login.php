<?php
# Daniel Aiken
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

define( 'USER_ACCOUNTS_FILENAME', 'dbases/useraccounts.txt' );
define( 'ADMIN_ACCOUNTS_FILENAME', 'dbases/adminaccounts.txt' );

$error_msg = '';
$already_logged_in = false;

# Takes a username and finds the corresponding information
# related to the username - full name and password hash string.
# Returns fan empty array if user is not in the system.
function find_user_information($user, $file_array)
{
   foreach($file_array as $line):
     list($username, $full_name, $password_hash) =
     explode("\t", $line);
     if($user === $username):
       $return_array = array($username, $full_name, $password_hash);
       return $return_array;
     endif; 
   endforeach;
   return array();
}

if(!(isset($_SESSION['username']) && isset($_SESSION['fullname']))):
  if( isset( $_POST['submit'] )):
    if(strpos($_POST['submit'], "User")): 
      $lines = file(USER_ACCOUNTS_FILENAME, FILE_IGNORE_NEW_LINES);
	else:
	  $lines = file(ADMIN_ACCOUNTS_FILENAME, FILE_IGNORE_NEW_LINES);
	endif;
    if(isset($_POST['username']) && 
    preg_match('|^\w+$|', $_POST['username']) &&
    isset($_POST['password']) && 
    preg_match('|^\S+$|', $_POST['password'])):
      $user_information = find_user_information($_POST['username'], $lines); 
      if(isset($user_information[0]) && password_verify($_POST['password'], $user_information[2])):
        $_SESSION['username'] =  $user_information[0];
        $_SESSION['fullname'] = $user_information[1];
		if(strpos($_POST['submit'], "User")):
		  $_SESSION['mode'] = "User";
		else:
		  $_SESSION['mode'] = "Administrator";
		endif;
        header( 'Location: home.php');
        exit;
      else:
        $error_msg = 'Username-password pair is invalid';
      endif;
    else:
      $error_msg = 'You must enter a valid username-password pair';
    endif;
  endif;
else:
  $already_logged_in = true;
endif; ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="author" content="Daniel Aiken" />
    <link rel="stylesheet" href="css/main.css" />
    <title>Login</title>
  </head>

  <body>
    <?php
      include("include/header.php");
      include("include/navbar.php");
      include("include/usefullinks.php");
    ?>

    <section>

    <?php if( $already_logged_in ): ?>
    <p>
      You are already logged in as <?= $_SESSION['fullname'] ?>
    </p>
    <p>
      <a href="home.php">OK</a>
    </p>

    <?php else:
    if( !empty( $error_msg )): ?>
    <p id="error"><?= $error_msg ?></p> 
    <?php endif; ?>
    <form action="login.php" method="post">
      <fieldset><legend>Log In</legend>
        <p>
          <label for="username">Username: </label>
          <input type="text" pattern="\w+" required 
           name="username" autofocus 
           placeholder="letters, digits, underscore" 
           id="username" />
        </p>
        <p>
          <label for="password">Password: </label>
          <input type="password" required name="password"
           placeholder="minimum length 5" pattern="[^ ]{5,}" 
           id="password" />
        </p>
        <p>
          <button type="submit" name="submit">Login as User</button>
          <button type="submit" name="submit">Login as Admin</button>
        </p>
      </fieldset>
    </form>
    <?php endif; ?>
    </section>
  </body>
</html>
