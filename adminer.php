<?php
    /*
        FILE: associazione/adminer.php
        Contenuto: Pagina di amministrazione dell'archivio dei soci
        AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
        ULTIMA MODIFICA: 12/12/2025
    */
    session_start();
    include("libs/funzioni.php");

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST["logout"])){
            // Se viene premuto il bottone di logout la pagina viene ricaricata tramite post-back, la sessione viene
            // terminata e si viene reindirizzati alla pagina di login
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit;
        }
    }

    if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
        // Se non è ancora stato eseguito l'accesso si viene reindirizzati alla pagina di login
        header("Location: login.php");
        exit;
    }
?>

<!DOCTYPE html>

<html lang="it">
    <head>
        <meta charset="utf-8">
        <link href="./style.css" rel="stylesheet" type="text/css">
        <link href="resources/favicon.ico" rel="icon" type="image/vnd.microsoft.icon">
        <title>Area riservata</title>
    </head>

    <body>
        <main>
            <div class="container">
                <a href="index.php">Torna alla pagina principale</a>
            </div>

            <div class="container">
                <h2>Lista dei soci in attesa di approvazione</h2>
                <div class="container-soci-table">
                    <table class="soci-table">
                        <tr class="soci-table">
                            <th class="soci-table">Cod. Socio</th>
                            <th class="soci-table">Cognome</th>
                            <th class="soci-table">Nome</th>
                            <th class="soci-table">Data di nascita</th>
                            <th class="soci-table">Sesso</th>
                            <th class="soci-table">Altezza [m]</th>
                            <th class="soci-table">Professione</th>
                            <th class="soci-table">Email</th>
                            <th class="soci-table">Telefono</th>
                            <th class="soci-table">Fototessera</th>
                            <th class="soci-table">Carta di Identità</th>
                            <th class="soci-table">Presentazione</th>
                            <th class="soci-table">Data e ora di iscrizione</th>
                            <th class="soci-table">Azioni</th>
                        </tr>
                        <?php
                            // La generazione del contenuto della tabella avviene dinamicamente tramite la funzione apposita
                            // che recupera i dati dai record memorizzati nel file dei soci
                            stampa_tabella_soci_da_approvare();
                        ?>
                    </table>
                </div>
            </div>

            <div class="container">
                <h2>Lista dei soci effettivi</h2>
                <div class="container-soci-table">
                    <table class="soci-table">
                        <tr class="soci-table">
                            <th class="soci-table">Cod. Socio</th>
                            <th class="soci-table">Cognome</th>
                            <th class="soci-table">Nome</th>
                            <th class="soci-table">Data di nascita</th>
                            <th class="soci-table">Sesso</th>
                            <th class="soci-table">Altezza [m]</th>
                            <th class="soci-table">Professione</th>
                            <th class="soci-table">Email</th>
                            <th class="soci-table">Telefono</th>
                            <th class="soci-table">Fototessera</th>
                            <th class="soci-table">Carta di Identità</th>
                            <th class="soci-table">Presentazione</th>
                            <th class="soci-table">Tessera</th>
                            <th class="soci-table">Data e ora di iscrizione</th>
                            <th class="soci-table">Azioni</th>
                        </tr>
                        <?php
                            // La generazione del contenuto della tabella avviene dinamicamente tramite la funzione apposita
                            // che recupera i dati dai record memorizzati nel file dei soci
                            stampa_tabella_soci_effettivi();
                        ?>
                    </table>
                </div>
            </div>

            <div class="container">
                <div class="container-right">
                    <b>ACCOUNT: <?php echo $_SESSION['email']; ?></b><br>
                    <form method='POST' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input class="button-red" type='submit' name='logout' value='Esci'>
                    </form>
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
