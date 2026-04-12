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
</head>

<?php
require_once("../utils/connect.php");

// check if there is a user already logged in, if so redirect them 
session_start();
$root = '..';
require_once("cookies.php");
if (isset($_SESSION['email'])) {
  header("Location: ../index.php");
} // redirect the user to the home page

$error = -1;
$message = '<a href="registrazione.php">Crea un account</a>';

$domain = 'alexandria.it'; //NOTE: placeholder

if (isset($_POST['submit'])) {
  if ($stmt = $conn->prepare('SELECT * FROM Utente WHERE Email = ?')) {
    $stmt->bind_param('s', $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if (mysqli_num_rows($result) == 1) {
      $user = $result->fetch_assoc();
      if (password_verify($_POST['password'], $user['Password'])) {
        $_SESSION['email'] = $user['Email'];
        $_SESSION['nome'] = $user['Nome'];
        $_SESSION['cognome'] = $user['Cognome'];
        $_SESSION['utenza'] = $user['Utenza'];
        setcookie('email', $user['Email'], time()+60*60*24*90, '/', $domain);
        setcookie('password', $user['Password'], time()+60*60*24*90, '/', $domain);
        // $message = '<h2>Accesso effettuato, puoi ora <a style="color: #2ac32d;" href="index.php">Navigare</a></h2>';
       
        if ($user['Utenza'] == 1) {
          //se utente admin, portare alla pagina dashboard
          header("Location: ../dashboard/dashboard.php");
        }else{
          header("Location: ../index.php");
        }
        
      } else {
        $error = 1;
        $err_message = "Incorrect password";
      }
    } else {
      $error = 2;
      $err_message = "User does not exist";
    }

  } else {
    $message = '<h2 style="color: red;">Errore, operazione fallita</h2>';
  }

  $stmt->close();
}

$conn->close();
?>

<body>
  <div class="container">
    <div class="left"></div>

    <div class="right">
      <div class="right-content">
        <form action="login.php" method="POST">
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
