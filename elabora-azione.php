<?php
/*
    FILE: associazione/elabora-azione.php
    Contenuto: Pagina di elaborazione delle possibili azioni collegate alle istanze della classe Socio memorizzate
               nell'archivio
    AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
    ULTIMA MODIFICA: 14/12/2025
*/
session_start();
include("libs/tessera.php");
use libs\Socio;

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]){
    // L'elaborazione deve essere seguita solo se la pagina è stata richiamata tramite post e solo se è stato eseguito l'accesso
    $soci = carica_soci(); // Carico in memoria l'archivio dei soci
    $socio_target = cerca_socio($_POST['id']); // Estraggo il socio specifico su cui devo eseguire l'azione

    if(isset($_POST["approva"])){
        // Se è stata richiesta una approvazione di nuovo socio
        $pos = array_search($socio_target, $soci); // Recupero l'indice del socio nell'array
        $socio_target->setStato(STATO_EFFETTIVO); // Modifico lo stato del socio
        $soci[$pos] = $socio_target; // Aggiorno l'istanza del socio presente nell'array

        // Aggiorno la lista dei soci convertendo le istanze in record e utilizzando l'apposita funzione
        $nuovi_soci = [];
        foreach($soci as $element){
            $nuovi_soci[] = $element->toString();
        }
        lista_aggiorna_soci($nuovi_soci);

        // Lancio la routine di creazione della tessera associativa per il socio approvato
        crea_tessera($socio_target);

        // Concluse tutte le operazioni reindirizzo alla pagina di amministrazione
        header("Location: adminer.php");
        exit;
    }

    if(isset($_POST["respingi"]) || isset($_POST["elimina"])){
        // Se è stato richiesto di respingere un socio registrato o di eliminare un socio approvato
        $pos = array_search($socio_target, $soci); // Recupero l'indice del socio nell'array
        if($pos !== false){
            // Se il socio è presente vado a eliminare i file a lui collegati qualora esistano
            $carta_identita = DIR_CARTA_IDENTITA . $soci[$pos]->getFileNameCartaidentita();
            if(file_exists($carta_identita)){
                unlink($carta_identita);
            }
            $fototessera = DIR_FOTOTESSERA . $soci[$pos]->getFileNameFototessera();
            if(file_exists($fototessera)){
                unlink($fototessera);
            }
            $presentazione = DIR_PRESENTAZIONE . $soci[$pos]->getFileNamePresentazione();
            if(file_exists($presentazione)){
                unlink($presentazione);
            }
            $qrcode = DIR_QR_CODE . $soci[$pos]->getFileNameQrcode();
            if(file_exists($qrcode)){
                unlink($qrcode);
            }
            $tessera = DIR_TESSERA . $soci[$pos]->getFileNameTessera();
            if(file_exists($tessera)){
                unlink($tessera);
            }

            // Rimuovo il socio dall'array e ne aggiorno le posizioni
            unset($soci[$pos]);
            $soci = array_values($soci);
        }

        // Aggiorno la lista dei soci convertendo le istanze in record e utilizzando l'apposita funzione
        $nuovi_soci = [];
        foreach($soci as $element){
            $nuovi_soci[] = $element->toString();
        }
        lista_aggiorna_soci($nuovi_soci);

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
