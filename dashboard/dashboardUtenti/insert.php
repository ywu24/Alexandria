<?php 
    $table = "Utente";
    require_once("../../utils/connect.php");
    if(empty($_POST['nome']) || empty($_POST['cognome'])|| empty($_POST['email'])|| empty($_POST['email'])|| empty($_POST['password'])){
        header("Location: aggiungiUtente.php?error=1");
        exit();
       
   }
   else{
   
        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $email = $_POST['email'];
        $ruolo = '4';
        if(empty($_POST['ruolo']))
            $ruolo = $_POST['ruolo'];
        $password = $_POST['password'];
        $password = password_hash($password, PASSWORD_BCRYPT);

        #echo $nome." ".$cognome." ".$email." ".$ruolo." ".$password;


        try {
            $sql = "INSERT INTO $table (`Nome`, `Cognome`, `Email`,`Utenza`, `Password`) VALUES ('$nome','$cognome','$email', '$ruolo','$password')";
            $conn->query($sql);
            header("Location: dashboardUtenti.php?aggiunto=1");
            
        } catch (mysqli_sql_exception $e) {
           
            header("Location: aggiungiUtente.php?error=2");
        } finally {
            $conn->close();
        }
        
   }
?>