<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrazione</title>
  <link rel="stylesheet" href="../css/registrazione.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
    rel="stylesheet" />
</head>

<body>
  <div class="container">
    <div class="left"></div>

    <div class="right">
      <div class="right-content">

        <form action="registrazione.php" method="POST">
          <h1>Crea un account</h1>
          <div>
            <h3>NOME</h3>
            <input type="text" name="nome" placeholder="Inserisci il tuo nome" required />
          </div>
          <div>
            <h3>COGNOME</h3>
            <input type="text" name="cognome" placeholder="Inserisci il tuo cognome" required />
          </div>
          <div>
            <h3>EMAIL</h3>
            <input type="text" name="email" placeholder="Inserisci il tuo indirizzo email" required />
          </div>
          <div>
            <h3>PASSWORD</h3>
            <input type="password" name="password" placeholder="Inserisci la tua password" required />
            <h4>La password deve avere lunghezza compresa tra 8 e 50 caratteri e contenere almeno un carattere speciale,
              es.
              !#$.,:;()</h4>
          </div>
          <div>
            <h3>CONFERMA PASSWORD</h3>
            <input type="password" name="passwordAgain" placeholder="Conferma la tua password" required />
          </div>
          <input type="submit" class="submit" name="submit" value="Registrami" />
          <br />

          <?php
          require_once("../utils/connect.php");
          $root = '..';
          require_once("cookies.php");

          // check to see if there is a user already logged in, if so redirect them 
          session_start();
          if (isset($_SESSION['email'])) {
            header("Location: ../index.php");
          } // redirect the user to the home page
          
          if (isset($_POST['submit'])) {
            $email = $_POST['email'];
            if ($stmt = $conn->prepare('SELECT Email FROM Utente WHERE Email = ?')) {
              $stmt->bind_param('s', $email);
              $stmt->execute();
              $result = $stmt->get_result();
              if (mysqli_num_rows($result) > 0) {
                echo '<h2 style="color: red;">Utente già registrato. <a style="color: #2ac32d;" href="login.php">Accedi</a></h2>';
              } else {
                $password = $_POST['password'];
                if (strlen($password) >= 8 && strpbrk($password, "!#$.,:;()")) {  
                  if (strlen($password) <= 50) {
                    if ($password === $_POST['passwordAgain']) {
                      if ($stmt1 = $conn->prepare('INSERT INTO Utente (Email,Nome,Cognome,Password,Utenza) VALUES(?,?,?,?,?)')) {
                        $password = password_hash($password, PASSWORD_BCRYPT);
                        $utenza = 4;
                        $stmt1->bind_param('ssssi', $email, $_POST['nome'], $_POST['cognome'], $password, $utenza);
                        $stmt1->execute();
                        echo '<h2>Account creato, puoi ora <a style="color: #2ac32d;" href="login.php">Accedere</a></h2>';
                      } else {
                        echo '<h2 style="color: red;">Errore, operazione fallita</h2>';
                      }
                      $stmt1->close();
                    } else {
                      echo '<h2 style="color: red;">Le password non corrispondono, si prega di riprovare</h2>';
                    }
                  } else {
                    echo '<h2 style="color: red;">La password supera il limite di lunghezza</h2>';
                  }
                } else {
                  echo '<h2 style="color: red;">La password non soddisfa i requisiti minimi di sicurezza</h2>';
                }
              }
            } else {
              echo '<h2 style="color: red;">Errore, operazione fallita</h2>';
            }
            $stmt->close();

          } else {
            echo '<a href="login.php">Accedi</a>';
          }

          $conn->close();
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