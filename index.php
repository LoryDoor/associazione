<?php
    /*
        FILE: associazione/index.php
        CONTENUTO: Pagina principale del sito
        AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
        ULTIMA MODIFICA: 31/01/2026
    */
    require_once "libs/funzioni.php";
    use libs\Socio;
?>

<!DOCTYPE html>

<html lang="it">
    <head>
        <meta charset="UTF-8">
        <link href="style.css" rel="stylesheet" type="text/css">
        <link href="resources/favicon.ico" rel="icon" type="image/vnd.microsoft.icon">
        <title>Associazione</title>
    </head>

    <body>
        <header>
            <div class="header-container">
                <h1>Associazione</h1>
                <p>
                    Benvenuto sul nostro sito.<br>
                    Siamo un'associazione di elite attiva in diversi ambiti e siamo sempre alla ricerca di nuovi soci.<br>
                </p>
            </div>
           <div class="header-container">
               <a class="button" href="adminer.php">Accesso amministratori</a>
           </div>
        </header>

        <main>
            <div class="container">
                <h2>Vuoi diventare un nostro socio?</h2>
                <p>
                    Puoi presentare la tua candidatura per diventare un socio della nostra associazione tramite un
                    apposito form raggiungibile qui:<br>
                    <a class="registrati" href="registrazione.html">REGISTRATI!</a><br>
                    Se la tua candidatura sarà accettata diventerai un socio effettivo e riceverai la nostra tessera.
                </p>
            </div>
            <div class="container">
                <h2>Questa è la lista dei nostri soci effettivi</h2>
                <div class="container-soci">
                    <?php
                        // Generazione dinamica della lista dei soci in stato EFFETTIVO
                        $soci = carica_soci(STATO_EFFETTIVO);
                        if(empty($soci)){
                            echo "Al momento non ci sono soci effettivi.";
                        }
                        else{
                            foreach ($soci as $socio) {
                                echo genera_card($socio->getCodiceSocio(), $socio->getCognome(), $socio->getNome());
                            }
                        }
                    ?>
                </div>
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
