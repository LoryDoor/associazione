<?php
/*
    FILE: associazione/libs/funzioni.php
    Contenuto: Libreria di costanti e funzioni del progetto "Associazione"
    AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
    ULTIMA MODIFICA: 12/12/2025
*/

require_once "connessione.php";
require_once "Socio.php";
use libs\Socio;

// Costanti comuni a tutto il codice
const DIR_DOCUMENTI_SOCI = "documenti_soci/";
const DIR_CARTA_IDENTITA = DIR_DOCUMENTI_SOCI."carta_identita/";
const DIR_FOTOTESSERA = DIR_DOCUMENTI_SOCI."fototessera/";
const DIR_PRESENTAZIONE =  DIR_DOCUMENTI_SOCI."presentazione/";
const DIR_TESSERA = DIR_DOCUMENTI_SOCI."tessera/";
const DIR_QR_CODE = DIR_TESSERA."qrcode/";
const STATO_REGISTRATO = "REGISTRATO";
const STATO_EFFETTIVO = "EFFETTIVO";
const ALTEZZA_MIN = 0.8;
const ALTEZZA_MAX = 2.3;
const REGEX_TEL = "/^(\+?\d{1,3})?[-\s]?\(?\d{2,4}\)?[-\s]?\d{2,4}[-\s]?\d{2,4}$/";
/*
    Contenuto della Regex:
     - (\+?\d{1,3})?: Opzionalmente, un prefisso internazionale (es. +39) con un segno + seguito da 1 a 3 cifre.
     - [-\s]?: Un trattino o uno spazio, opzionale.
     - \(?\d{2,4}\)?: Un'area del numero, opzionalmente tra parentesi.
     - [-\s]?: Un altro trattino o spazio, opzionale.
     - \d{2,4}: Altre cifre (generalmente 2 o 4 cifre).
     - [-\s]?: Un altro trattino o spazio, opzionale.
     - \d{2,4}: Le ultime cifre.
 */

// Esegue la procedura di autenticazione per l'accesso all'area riservata
function login($email, $password): array
{
    global $mysqli;
    $query = "CALL cerca_persona('" . $email . "', '" . $password . "')";

    $result = null;
    try{
        $result = $mysqli->query($query);
    }
    catch(mysqli_sql_exception $ex){
        echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
    }

    if($result->num_rows == 1){
        $data = $result->fetch_assoc();
        return [
            "email" => $data["Email"],
            "nome" => $data["NomeUtente"]
        ];
    }

    return [];
}

// Partendo da un percorso di base fornito come parametro e una directory di destinazione per quel file, la funzione
// genera un percorso sicuramente univoco grazie a un contatore e lo restituisce
function genera_file_path($base_file_name, $dir): string
{
    $cont = 1;

    do{
        $file_path = $dir . pathinfo($base_file_name, PATHINFO_FILENAME) . "_"
            . $cont++ . "." . pathinfo($base_file_name, PATHINFO_EXTENSION);
    }while(file_exists($file_path));

    return $file_path;
}

// Aggiunge una nuova tupla alla tabella soci e restituisce un flag booleano sull'esito dell'inserimento
function aggiungi_socio(Socio $socio) : bool
{
    global $mysqli;

    $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'db_associazione' AND TABLE_NAME = 'Soci'";
    try{
        $result = $mysqli->query($query);
    }
    catch(mysqli_sql_exception $ex){
        echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
        return false;
    }

    if($result->num_rows > 0){
        $result->fetch_array(); // consumo la prima riga che contiene il campo ID

        $arrCampi = [];
        while($data = $result->fetch_array()){
            $arrCampi[] = $data['COLUMN_NAME'];
        }
        $campiTabella = implode(", ", $arrCampi);

        $query = "INSERT INTO Soci ($campiTabella) VALUES " . $socio->toInsertRecord();
        try{
            $result = $mysqli->query($query);
        }
        catch(mysqli_sql_exception $ex){
            echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
            return false;
        }

        if($result){
            echo "<div class='log'>Inserimento dei dati avvenuto con successo";
            return true;
        }
    }
    else {
        echo "<div class='warning'>La query non ha prodotto risultati: " . $mysqli->error . "</div>";
        return false;
    }

    return false;
}

// Restituisce un testo in formato HTML che rappresenta la "card del socio" visibile nella pagina principale
function genera_card($id, $cognome, $nome) : string
{
    $id_url = urlencode($id);
    $cognome = ucfirst($cognome);
    $nome = ucfirst($nome);
    return "
        <div class='card-socio'>
            $id - $nome $cognome<br>
            <a href='informazioni-socio.php?id=$id_url'>Visualizza profilo</a>
        </div>
    ";
}

// Crea un array di istanze della classe Socio partendo dai record presenti nel file dei soci
function carica_soci($stato = null): array
{
    global $mysqli;

    $query = "SELECT * FROM Soci";
    if($stato != null){
        $query = "SELECT * FROM Soci WHERE Stato = '" . $stato . "'";
    }
    try {
        $result = $mysqli->query($query);
    }
    catch (mysqli_sql_exception $ex){
        echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
    }

    $soci = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $soci[] = Socio::crea_da_result_set($row);
        }
    }

    return $soci;
}

// Cerca il socio il cui ID è passato come parametro e lo restituisce, se non lo trova restituisce null.
function cerca_socio($id){
    global $mysqli;
    $query = "SELECT * FROM Soci WHERE ID = '" . $id . "'";

    try{
        $result = $mysqli->query($query);
    }
    catch(mysqli_sql_exception $ex){
        echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
    }

    if($result->num_rows > 0){
        $row = $result->fetch_array();
        return Socio::crea_da_result_set($row);
    }

    return null;
}

// Stampa su monitor la presentazione del socio in un formato gradevole per l'utente
function stampa_presentazione($file_path){
    $content = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($content as $line){
        echo $line . "<br>";
    }
}

// Stampa su monitor una tabella secondo il formato determinato nella pagina adminer.php contenente la lista dei soci in
// stato di REGISTRATO o EFFETTIVO in base al valore passato come parametro
function stampa_tabella_soci($stato){
    global $mysqli;
    $query = "SELECT * FROM Soci WHERE Stato = '$stato'";

    try{
        $result = $mysqli->query($query);
    }
    catch(mysqli_sql_exception $ex){
        echo "<div class='error'>Errore nell'esecuzione della query: " . $ex->getMessage() . "</div>";
    }

    if($result->num_rows > 0){
        while($row = $result->fetch_array()){
            echo "
            <tr class='soci-table'>
                <td class='soci-table'>".$row[0]."</td>
                <td class='soci-table'>".ucwords($row[1])."</td>
                <td class='soci-table'>".ucwords($row[2])."</td>
                <td class='soci-table'>".$row[3]."</td>
                <td class='soci-table'>".$row[4]."</td>
                <td class='soci-table'>".$row[5]."</td>
                <td class='soci-table'>".ucwords($row[6])."</td>
                <td class='soci-table'>".$row[7]."</td>
                <td class='soci-table'>".$row[8]."</td>
            ";

            if($stato == STATO_REGISTRATO){
                $href_fototessera = DIR_FOTOTESSERA . $row[9];
                $href_cartaidentia = DIR_CARTA_IDENTITA . $row[10];
                $href_presentazione = DIR_PRESENTAZIONE . $row[11];

                echo "
                    <td class='soci-table'><a href='$href_fototessera'>Fototessera</a></td>
                    <td class='soci-table'><a href='$href_cartaidentia'>Carta di Identità</a></td>
                    <td class='soci-table'><a href='$href_presentazione'>Presentazione</a></td>
                    <td class='soci-table'>".$row[14]. "</td>
                    <td class='soci-table'>
                        <!-- Il form contiene dei bottoni generati dinamicamente che collegano alle specifiche azioni gestite
                         dalla pagina elabora-azione.php -->
                        <form method='post' action='elabora-azione.php'>
                            <input class='button-green' type='submit' name='approva' value='Approva'>
                            <input class='button-red' type='submit' name='respingi' value='Respingi'>
                            <input type='hidden' name='id' value='$row[0]'>
                        </form>
                    </td>
                </tr>
                ";
            }

            if($stato == STATO_EFFETTIVO){
                $href_fototessera = DIR_FOTOTESSERA . $row[9];
                $href_cartaidentia = DIR_CARTA_IDENTITA . $row[10];
                $href_presentazione = DIR_PRESENTAZIONE . $row[11];
                $href_tessera = DIR_TESSERA . $row[13];

                echo "
                    <td class='soci-table'><a href='$href_fototessera'>Fototessera</a></td>
                    <td class='soci-table'><a href='$href_cartaidentia'>Carta di Identità</a></td>
                    <td class='soci-table'><a href='$href_presentazione'>Presentazione</a></td>
                    <td class='soci-table'><a href='$href_tessera'>Tessera</a></td>
                    <td class='soci-table'>".$row[14]."</td>
                    <td class='soci-table'>
                        <form method='post' action='elabora-azione.php'>
                            <!-- Il form contiene dei bottoni generati dinamicamente che collegano alle specifiche azioni gestite
                             dalla pagina elabora-azione.php -->
                            <input class='button-red' type='submit' name='elimina' value='Elimina'>
                            <input type='hidden' name='id' value='$row[0]'>
                        </form>
                    </td>
                </tr>
                ";
            }
        }
    }
}
