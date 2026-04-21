 
 
 <?php 

    if (!isset($_POST['id'])){
        echo "Errore: prenotazione non definita";
        die();
    }
    $giorniPrenotazione= 30;
    
    $id = (int) $_POST['id'];
    require_once("../../utils/connect.php");
    require_once("../../auth/cookies.php");

    switch (true) {
        case isset($_POST['conferma']):
            #echo "ID = " . $id;                     
            $conn->query("UPDATE Prenotazione SET InizioPrestito = CURDATE() WHERE idPrenotazione = $id AND CURDATE()<=FinePrenotazione");
            if($conn->affected_rows >0){
                $conn->query("UPDATE `Prenotazione` SET `FineAttesa` = ADDDATE(CURDATE(), INTERVAL $giorniPrenotazione DAY) WHERE `Prenotazione`.`idPrenotazione` = $id");
                echo "ok";
                }
            else{
                ####segnalale errore: prenotazione scaduta.
                echo "Errore prenotazione Scaduta?";

            }
                break;
        case isset($_POST['termina']):
            try{
                $conn->query("UPDATE Prenotazione SET FinePrestito = CURDATE() WHERE idPrenotazione = $id");
                if($conn->affected_rows>0){
                    $conn->query("UPDATE copiaLibro, Prenotazione SET copiaLibro.Stato = '1' WHERE copiaLibro.idCopia = Prenotazione.idCopia AND Prenotazione.idPrenotazione = $id");
                    echo "ok prestito terminato con successo!";
                }
                else{
                    echo "errore: " . $id;
                }
            }
            catch(Exception $e){
                echo "errore " . $e ;
            }
            finally{
                
            }
            break;
         case isset($_POST['elimina']):
            try{
                $res = $conn->query("SELECT idCopia, Email FROM Prenotazione WHERE idPrenotazione = $id");
                $row = $res->fetch_assoc();

                if ($row) {
                    $idCopia = $row['idCopia'];
                    $email = $row['Email'];
                    
                    $result =$conn->query("SELECT id FROM Utente WHERE Email=  '$email'");
                    $idUtente = ($result->fetch_assoc())['id'];
                    // 2. Eliminiamo la prenotazione
                    $conn->query("DELETE FROM Prenotazione WHERE idPrenotazione = $id");

                    // 3. Riportiamo la copia del libro a 'disponibile' (Stato 1)
                    $conn->query("UPDATE copiaLibro SET Stato = '1' WHERE idCopia = $idCopia");

                    
                    echo "ok " . $idUtente;
                } else {
                    echo "Errore: prenotazione non trovata";
                    }
            } catch (Exception $e) {
                $conn->rollback();
                echo "Errore durante l'eliminazione: " . $e;
            }
            break;
           

        default:
            echo "ID = " . $id;
            break;
    }
?>