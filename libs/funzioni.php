<?php
/*
    FILE: associazione/libs/funzioni.php
    Contenuto: Libreria di costanti e funzioni del progetto "Associazione"
    AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
    ULTIMA MODIFICA: 12/12/2025
*/

include("Socio.php");
use libs\Socio;

// Costanti comuni a tutto il codice
const DIR_DOCUMENTI_SOCI = "documenti_soci/";
const DIR_CARTA_IDENTITA = DIR_DOCUMENTI_SOCI."carta_identita/";
const DIR_FOTOTESSERA = DIR_DOCUMENTI_SOCI."fototessera/";
const DIR_PRESENTAZIONE =  DIR_DOCUMENTI_SOCI."presentazione/";
const DIR_TESSERA = DIR_DOCUMENTI_SOCI."tessera/";
const DIR_QR_CODE = DIR_TESSERA."qrcode/";
const PATH_LISTA_SOCI = DIR_DOCUMENTI_SOCI."soci.txt";
const FILE_AMMINISTRATORI = "./amministratori.txt";
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

// Recupera gli utenti dal file degli amministratori per la procedura di autenticazione
function caricaUtenti(): array
{
    $utenti = [];

    try{
        $file = fopen(FILE_AMMINISTRATORI, "r");
        while(!feof($file)){
            $line = fgets($file);
            $data = explode(";", $line);

            $utenti[trim($data[1])] = [
                "nome" => trim($data[0]),
                "password" => trim($data[2])
            ];
        }
        fclose($file);
    }
    catch(Exception $ex){
        echo "<div class='errore'>".$ex->getMessage()."</div>";
    }

    return $utenti;
}

// Esegue la procedura di autenticazione per l'accesso all'area riservata
function verifica($email, $password): array
{
    $utenti = caricaUtenti();

    if (array_key_exists($email, $utenti)) {
        if(password_verify($password, $utenti[$email]["password"])){
            return [
                "email" => $email,
                "nome" => $utenti[$email]["nome"]
            ];
        }
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

// Controlla se esiste un socio che si sia già registrato con l'email passata come parametro
function email_presente($email): bool
{
    $content = file(PATH_LISTA_SOCI, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

    foreach ($content as $line) {
        $data = explode(";", $line);
        if($data[7] == $email){
            return true;
        }
    }

    return false;
}

// Modifica il file della lista dei soci per aggiungere un nuovo record
function lista_aggiungi_socio(Socio $socio){
    $file = fopen(PATH_LISTA_SOCI, "a");
    fwrite($file, $socio->toString());
    fclose($file);
}

// Modifica il file della lista dei soci quando si modificano i valori di uno dei record o quando uno di questi viene
// eliminato
function lista_aggiorna_soci($nuovi_soci){
    $file = fopen(PATH_LISTA_SOCI, "w");
    foreach($nuovi_soci as $line){
        fwrite($file, $line);
    }
    fclose($file);
}

// Restituisce il numero di socio successivo a quello memorizzato nell'ultimo record del file dei soci
function conta_soci(): int
{
	$content = file(PATH_LISTA_SOCI, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    $last_line = $content[count($content) - 1];
    $data = explode(";", $last_line);
    return ((int) $data[0]);
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
function carica_soci(): array
{
    $content = file(PATH_LISTA_SOCI, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    $soci = [];
    foreach ($content as $line) {
        $soci[] = Socio::crea_da_linea(trim($line));
    }

    return $soci;
}

// Cerca il socio il cui ID è passato come parametro e lo restituisce, se non lo trova restituisce null.
function cerca_socio($id){
    $soci = carica_soci();

    foreach ($soci as $socio){
        if($socio->getCodiceSocio() == $id){
            return $socio;
        }
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
// stato di REGISTRATO
function stampa_tabella_soci_da_approvare(){
    $content = file(PATH_LISTA_SOCI, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    foreach ($content as $line){
        $data = explode(";", $line);
        if($data[15] == STATO_REGISTRATO){
            echo "
            <tr class='soci-table'>
                <td class='soci-table'>".$data[0]."</td>
                <td class='soci-table'>".ucwords($data[1])."</td>
                <td class='soci-table'>".ucwords($data[2])."</td>
                <td class='soci-table'>".$data[3]."</td>
                <td class='soci-table'>".$data[4]."</td>
                <td class='soci-table'>".$data[5]."</td>
                <td class='soci-table'>".ucwords($data[6])."</td>
                <td class='soci-table'>".$data[7]."</td>
                <td class='soci-table'>".$data[8]."</td>
            ";

            $href_fototessera = DIR_FOTOTESSERA . $data[9];
            $href_cartaidentia = DIR_CARTA_IDENTITA . $data[10];
            $href_presentazione = DIR_PRESENTAZIONE . $data[11];

            echo "
                <td class='soci-table'><a href='$href_fototessera'>Fototessera</a></td>
                <td class='soci-table'><a href='$href_cartaidentia'>Carta di Identità</a></td>
                <td class='soci-table'><a href='$href_presentazione'>Presentazione</a></td>
                <td class='soci-table'>".$data[14]. "</td>
                <td class='soci-table'>
                    <!-- Il form contiene dei bottoni generati dinamicamente che collegano alle specifiche azioni gestite
                     dalla pagina elabora-azione.php -->
                    <form method='post' action='elabora-azione.php'>
                        <input class='button-green' type='submit' name='approva' value='Approva'>
                        <input class='button-red' type='submit' name='respingi' value='Respingi'>
                        <input type='hidden' name='id' value='$data[0]'>
                    </form>
                </td>
            </tr>
            ";
        }
    }
}

// Stampa su monitor una tabella secondo il formato determinato nella pagina adminer.php contenente la lista dei soci in
// stato di EFFETTIVO
function stampa_tabella_soci_effettivi(){
    $content = file(PATH_LISTA_SOCI, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    foreach ($content as $line){
        $data = explode(";", $line);
        if($data[15] == STATO_EFFETTIVO){
            echo "
            <tr class='soci-table'>
                <td class='soci-table'>".$data[0]."</td>
                <td class='soci-table'>".$data[1]."</td>
                <td class='soci-table'>".$data[2]."</td>
                <td class='soci-table'>".$data[3]."</td>
                <td class='soci-table'>".$data[4]."</td>
                <td class='soci-table'>".$data[5]."</td>
                <td class='soci-table'>".$data[6]."</td>
                <td class='soci-table'>".$data[7]."</td>
                <td class='soci-table'>".$data[8]."</td>
            ";

            $href_fototessera = DIR_FOTOTESSERA . $data[9];
            $href_cartaidentia = DIR_CARTA_IDENTITA . $data[10];
            $href_presentazione = DIR_PRESENTAZIONE . $data[11];
            $href_tessera = DIR_TESSERA . $data[13];

            echo "
                <td class='soci-table'><a href='$href_fototessera'>Fototessera</a></td>
                <td class='soci-table'><a href='$href_cartaidentia'>Carta di Identità</a></td>
                <td class='soci-table'><a href='$href_presentazione'>Presentazione</a></td>
                <td class='soci-table'><a href='$href_tessera'>Tessera</a></td>
                <td class='soci-table'>".$data[14]."</td>
                <td class='soci-table'>
                    <form method='post' action='elabora-azione.php'>
                        <!-- Il form contiene dei bottoni generati dinamicamente che collegano alle specifiche azioni gestite
                         dalla pagina elabora-azione.php -->
                        <input class='button-red' type='submit' name='elimina' value='Elimina'>
                        <input type='hidden' name='id' value='$data[0]'>
                    </form>
                </td>
            </tr>
            ";
        }
    }
}
