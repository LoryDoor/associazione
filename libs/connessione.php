<?php
/*
    FILE: associazione/libs/connessione.php
    CONTENUTO: Script di gestione della connessione al DB
    AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
    ULTIMA MODIFICA: 30/01/2026
*/
const DB_SERVER = "localhost";
const DB_USER = "db12778";
const DB_PASS = "Auzue9vq";
const DB_NAME = "db_associazione";

$mysqli = null;

try{
    $mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
}
catch(mysqli_sql_exception $ex){
    echo "<div class='error'>Errore durante la connessione al DB: " . $ex->getMessage() . "</div>";
}
