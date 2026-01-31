<?php
/*
    FILE: associazione/elabora-azione.php
    Contenuto: Pagina di elaborazione delle possibili azioni collegate alle istanze della classe Socio memorizzate
               nel DB
    AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
    ULTIMA MODIFICA: 31/01/2026
*/
session_start();
require_once "libs/tessera.php";
use libs\Socio;

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]){
    // L'elaborazione deve essere seguita solo se la pagina è stata richiamata tramite post e solo se è stato eseguito l'accesso
    global $mysqli;

    if(isset($_POST["approva"])){
        // Se è stata richiesta una approvazione di nuovo socio utilizziamo l'apposita stored procedure per commutarne
        // lo stato.
        $query = "CALL rendi_effettivo('" . $_POST['id'] . "')";
        try{
            $result = $mysqli->query($query);
        }
        catch(mysqli_sql_exception $ex){
            echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
        }

        // Se la query produce un cambiamento sulla tabella, provvediamo a creare la tessera associativa
        if($result && $mysqli->affected_rows == 1){
            crea_tessera(cerca_socio($_POST['id']));
        }

        // Concluse tutte le operazioni reindirizzo alla pagina di amministrazione
        header("Location: adminer.php");
        exit;
    }

    if(isset($_POST["respingi"]) || isset($_POST["elimina"])){
        // Se è stato richiesto di respingere un socio registrato o di eliminare un socio approvato
        $query = "DELETE FROM Soci WHERE ID = '" . $_POST['id'] . "'";

        $socio_target = cerca_socio($_POST['id']);
        if($socio_target != null) {
            // Se il socio esiste, eseguo la query di cancellazione della tupla dal DB
            try {
                $result = $mysqli->query($query);
            }
            catch (mysqli_sql_exception $ex) {
                echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
            }

            if ($result && $mysqli->affected_rows == 1) {
                // Se la query ha esito positivo procedo ad eliminare i file associati al socio qualora esistano
                $carta_identita = DIR_CARTA_IDENTITA . $socio_target->getFileNameCartaidentita();
                if (file_exists($carta_identita)) {
                    unlink($carta_identita);
                }
                $fototessera = DIR_FOTOTESSERA . $socio_target->getFileNameFototessera();
                if (file_exists($fototessera)) {
                    unlink($fototessera);
                }
                $presentazione = DIR_PRESENTAZIONE . $socio_target->getFileNamePresentazione();
                if (file_exists($presentazione)) {
                    unlink($presentazione);
                }
                $qrcode = DIR_QR_CODE . $socio_target->getFileNameQrcode();
                if (file_exists($qrcode)) {
                    unlink($qrcode);
                }
                $tessera = DIR_TESSERA . $socio_target->getFileNameTessera();
                if (file_exists($tessera)) {
                    unlink($tessera);
                }
            }
        }

        // Concluse tutte le operazioni reindirizzo alla pagina di amministrazione
        header("Location: adminer.php");
        exit;
    }
}
else{
    // Se la pagina non è stata richiamata tramite post o non è stato eseguito l'accesso reindirizzo alla pagina di
    // amministrazione a sua volta non accessibile se non si esegue l'accesso.
    header("Location: login.php");
    exit;
}
