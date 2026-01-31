<?php
    /*
        FILE: associazione/informazioni-socio.php
        Contenuto: Pagina dedicata ai soci effettivi in cui è possibile visualizzare le loro informazioni pubbliche
        AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
        ULTIMA MODIFICA: 12/12/2025
    */
    require_once "libs/funzioni.php";
    use libs\Socio;

    if(!isset($_GET["id"])){
        header("Location: index.php");
        exit;
    }

    // Ricerco il socio all'interno dell'archivio
    $socio = cerca_socio($_GET['id']);
    $errore = false;

    if($socio == null){
        // Se non viene trovato alcun socio imposto il flag di errore a true
        $errore = true;
    }
    else{
        // Se il socio trovato non è in stato EFFETTIVO imposto il flag di errore a true
        if($socio->getStato() != STATO_EFFETTIVO){
            $errore = true;
        }
    }
?>

<!DOCTYPE html>

<html lang="it">
    <head>
        <meta charset="UTF-8">
        <link href="style.css" rel="stylesheet" type="text/css">
        <link href="resources/favicon.ico" rel="icon" type="image/vnd.microsoft.icon">
        <title><?php echo $socio->getCodiceSocio() . " - " . ucwords($socio->getCognome()) . " " . ucwords($socio->getNome()) ?></title>
    </head>

    <?php
        if($errore){
            // Se si è verificato un errore lo comunico all'utente e termino lo script, altrimenti vengono stampate a
            // video le informazioni sul socio
            echo "<div class='container'>
                <div class='errore'>Non è possibile generare la pagina di questo socio.</div>
                <a href='index.php'>Torna alla pagina principale</a>
            </div>";
            exit;
        }
    ?>

    <body>
        <main>
            <div class="container">
                <a href="index.php">Torna alla pagina principale</a>
            </div>

            <div class="container">
                <h1>
                    Informazioni sul socio <?php echo ucwords($socio->getCognome()) . " " .  ucwords($socio->getNome()) ?>
                </h1>
                <table>
                    <tr class="info-socio">
                        <td class="fototessera" rowspan="5">
                            <img class="fototessera"
                                    src="<?php echo DIR_FOTOTESSERA . $socio->getFileNameFototessera() ?>"
                                    alt="Fototessera di <?php echo ucwords($socio->getNome()) . " " .  ucwords($socio->getCognome()) ?>"
                            >
                        </td>

                        <th class="info-socio">Codice socio:</th>
                        <td class="info-socio"><?php echo str_pad($socio->getCodiceSocio(), 4, "0", STR_PAD_LEFT); ?></td>
                    </tr>
                    <tr class="info-socio">
                        <th class="info-socio">Cognome:</th>
                        <td class="info-socio"><?php echo ucwords($socio->getCognome()) ?></td>
                    </tr>
                    <tr class="info-socio">
                        <th class="info-socio">Nome:</th>
                        <td class="info-socio"><?php echo ucwords($socio->getNome()) ?></td>
                    </tr>
                    <tr class="info-socio">
                        <th class="info-socio">Età:</th>
                        <td class="info-socio">
                            <?php
                                $eta = (new DateTime())->diff($socio->getDataNascita())->y;
                                echo "$eta anni";
                            ?>
                        </td>
                    </tr>
                    <tr class="info-socio">
                        <th class="info-socio">Giorni dall'iscrizione:</th>
                        <td class="info-socio"><?php echo (new DateTime())->diff($socio->getDataOraIscrizione())->days ?></td>
                    </tr>
                </table>
            </div>

            <div class="container">
                <h2>La mia presentazione</h2>
                <p class="presentazione">
                    <?php stampa_presentazione(DIR_PRESENTAZIONE . $socio->getFileNamePresentazione()); ?>
                </p>
            </div>
        </main>

        <footer>
            Sito realizzato a scopo didattico.
            <br>Tutte le funzioni sono a solo scopo dimostrativo.
            <br><br>
            Autore: Lorenzo Porta,  Classe 5FIN, AS: 2025/2026
            <br>ITT "G. Fauser" Via G. B. Ricci, 14, 28100, Novara, Italia.
        </footer>
    </body>
</html>
