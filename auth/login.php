<?php
session_start();

$root = '..';
$error = -1;
$message = '<a href="registrazione.php">Crea un account</a>';
$domain = 'alexandria.it'; //NOTE: placeholder

try {
  require_once("../utils/connect.php");
  $database = DatabaseConnection::getInstance();
  $pdo = $database->getConnection();
  require_once("cookies.php");
} catch (Exception $e) {
  error_log('[login.php] DB connection failed: ' . $e->getMessage());
  $error = 3;
  $err_message = 'Service unavailable, please try again later';
}

if (isset($_SESSION['email'])) {
  header("Location: ../index.php");
  exit; // redirect the user to the home page
}

if ($error === -1 && isset($_POST['submit'])) {
  try {
    $query = $pdo->prepare('SELECT * FROM Utente WHERE Email = :email');
    $query->bindParam(':email', $_POST['email']);
    $query->execute();
    $result = $query->fetch();
    $query->closeCursor();
    if ($result) {
      $user = $result;
      if (password_verify($_POST['password'], $user['Password'])) {
        $_SESSION['email'] = $user['Email'];
        $_SESSION['nome'] = $user['Nome'];
        $_SESSION['cognome'] = $user['Cognome'];
        $_SESSION['utenza'] = $user['Utenza'];
        setcookie('email', $user['Email'], time() + 60 * 60 * 24 * 90, '/', $domain);
        setcookie('password', $user['Password'], time() + 60 * 60 * 24 * 90, '/', $domain);
        // $message = '<h2>Accesso effettuato, puoi ora <a style="color: #2ac32d;" href="index.php">Navigare</a></h2>';

        if ($user['Utenza'] == 1 || $user['Utenza'] == 2) {
          //se utente admin, portare alla pagina dashboard
          header("Location: ../dashboard/dashboard.php");
        } else {
          header("Location: ../index.php");
        }
        exit;

      } else {
        $_SESSION['login_error'] = ['type' => 1, 'msg' => "Incorrect password"];
        header("Location: login.php");
        exit;
      }
    } else {
      $_SESSION['login_error'] = ['type' => 2, 'msg' => "User does not exist"];
      header("Location: login.php");
      exit;
    }
    
  } catch (Exception $e) {
    error_log('[login.php] Query failed: ' . $e->getMessage());
    $_SESSION['login_error'] = ['type' => 1, 'msg' => 'Something went wrong, please try again later'];
    header("Location: login.php");
    exit;
  }
}

if (isset($_SESSION['login_error'])) {
  $error = $_SESSION['login_error']['type'];
  $err_message = $_SESSION['login_error']['msg'];
  unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Accedi</title>
  <link rel="stylesheet" href="../css/login.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
    rel="stylesheet" />
    <script src="login.js"></script>
</head>

<body>
  <div class="container">
    <div class="left"></div>

    <div class="right">
      <div class="right-content">
        <form action="login.php" id="myform" method="POST">
          <h1>Accedi</h1>
          <div class="inputs">
            <div class="field">
              <h3>EMAIL</h3>
              <input type="text" name="email" placeholder="Inserisci il tuo indirizzo email" required />

              <?php
              if ($error == 2) {
                echo "<h2 style='color: red;'>$err_message</h2>";
              }
              ?>

            </div>
            <div class="field">
              <h3>PASSWORD</h3>
              <input type="password" name="password" placeholder="Inserisci la tua password" required />

              <?php
              if ($error == 1) {
                echo "<h2 style='color: red;'>$err_message</h2>";
              }
              ?>

            </div>
          </div>
          <input type="submit" name="submit" class="submit" value="Accedi" />
          <br />

          <?php
          echo "<h2>$message</h2>";
          ?>

          <center>
            <h4 class="privacy">Privacy · Termini e Condizioni</h4>
          </center>
        </form>
      </div>
    </div>
  </div>
</body>

</html>