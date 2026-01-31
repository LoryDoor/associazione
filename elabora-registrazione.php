<?php
    /*
        FILE: associazione/elabora-registrazione.php
        Contenuto: Pagina di validazione dei dati inseriti nel form di registrazione e di responso per l'utente
        AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
        ULTIMA MODIFICA: 31/01/2026
    */

    require_once "libs/funzioni.php";
    use libs\Socio;

    if($_SERVER["REQUEST_METHOD"] != "POST"){
        // È possibile accedere alla pagina sono tramite richiesta POST
        header("Location: index.php");
        exit;
    }
?>

<!DOCTYPE html>

<html lang="it">
    <head>
        <meta charset="utf-8">
        <link href="style.css" rel="stylesheet" type="text/css">
        <link href="resources/favicon.ico" rel="icon" type="image/vnd.microsoft.icon">
        <title>Esito registrazione</title>
    </head>

    <body>
        <main>
            <div class="container">
                <h1>Esito della registrazione</h1>
                <?php
                    $errore = false;

                    if (empty($_POST["cognome"])) {
                        // Il cognome deve essere una stringa non-blank
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotCognome&quot.</div>";
                    }

                    if(empty($_POST["nome"])) {
                        // Il nome deve essere una stringa non-blank
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotNome&quot.</div>";
                    }

                    if(!isset($_POST["data-nascita"])) {
                        // La data di nascita deve essere valorizzata
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotData di nascita&quot.</div>";
                    }

                    if(!isset($_POST["sesso"])){
                        // È obbligatorio scegliere un'opzione per il sesso
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Selezionare un'opzione nel campo &quotSesso&quot.</div>";
                    }

                    if(empty($_POST["altezza"])) {
                        // L'altezza deve essere valorizzata ...
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotAltezza&quot.</div>";
                    }
                    else if($_POST["altezza"] < ALTEZZA_MIN || $_POST["altezza"] > ALTEZZA_MAX){
                        // ... e deve avere un valore compreso tra un massimo e un minimo
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotAltezza&quot.</div>";
                    }

                    if(!isset($_POST["professione"])){
                        // È obbligatorio scegliere un'opzione per la professione
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Selezionare un'opzione nel campo &quotProfessione&quot.</div>";
                    }

                    if(empty($_POST["email"])) {
                        // L'email deve essere valorizzata, ...
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotEmail&quot.</div>";
                    }
                    else if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
                        // ... deve rispettare il formato standard di un'email
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotEmail&quot.</div>";
                    }

                    if(empty($_POST["telefono"])){
                        // Il telefono deve essere valorizzato e ...
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotTelefono&quot.</div>";
                    }
                    else if(!preg_match(REGEX_TEL, $_POST["telefono"])){
                        // ... e deve rispettare il formato standard di un numero telefonico (verifica tramite regex)
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotTelefono&quot.</div>";
                    }

                    if($_FILES["fototessera"]["type"] != "image/jpg" && $_FILES["fototessera"]["type"] != "image/jpeg"){
                        // La fototessera deve essere unicamente in formato .jpg o .jpeg
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Il file caricato nel campo &quotFototessera&quot è in un formato non supportato.</div>";
                    }

                    if($_FILES["cartaidentita"]["type"] != "application/pdf"){
                        // La carta di identià deve essere unicamente in formato .pdf
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Il file caricato nel campo &quotCarta di identità&quot è in un formato non supportato.</div>";
                    }

                    if(empty($_POST["presentazione"])){
                        // La presentazione deve essere valorizzata
                        $errore = true;
                        echo "<div class='errore'>ERRORE: Compilare opportunamente il campo &quotPresentati&quot.</div>";
                    }

                    if($errore){
                        // Se è presente un errore viene stampato un messaggio di errore e il link per tornare al form
                        echo "<a href='registrazione.html'>Torna al form</a>";
                        exit;
                    }
                    else{
                        //Altrimenti si procede al caricamento dei file sul server
                        $errore_file = false; // Variabile per verificare la presenza di errori sulle operazioni con i file

                        // Generazione del percorso di salvataggio del file della fototessera
                        $path_fototessera = "fototessera_".strtolower($_POST["cognome"])."_".strtolower($_POST["nome"]).".jpg";
                        $path_fototessera = genera_file_path($path_fototessera, DIR_FOTOTESSERA);
                        // Spostamento del file della fototessera dalla posizione temporanea di upload alla posizione definitiva
                        $errore_file = !move_uploaded_file($_FILES["fototessera"]["tmp_name"], $path_fototessera);

                        // Generazione del percorso di salvataggio del file della carta di identità
                        $path_cartaidentita = "carta_identita_".strtolower($_POST["cognome"])."_".strtolower($_POST["nome"]).".pdf";
                        $path_cartaidentita = genera_file_path($path_cartaidentita, DIR_CARTA_IDENTITA);
                        // Spostamento del file della carta di identità dalla posizione temporanea di upload alla posizione definitiva
                        $errore_file = !move_uploaded_file($_FILES["cartaidentita"]["tmp_name"], $path_cartaidentita);

                        // Generazione del percorso di salvataggio del file della presentazione
                        $path_presentazione = "presentazione_".strtolower($_POST["cognome"])."_".strtolower($_POST["nome"]).".txt";
                        $path_presentazione = genera_file_path($path_presentazione, DIR_PRESENTAZIONE);
                        // Creazione del file e scrittura sul file della presentazione digitata dall'utente
                        $file =  fopen($path_presentazione, "w");
                        if(!fwrite($file, $_POST["presentazione"])){
                            $errore_file = true;
                        }
                        fclose($file);

                        // Generazione del percorso di salvataggio del file del QR code
                        $path_qrcode = "qrcode_".strtolower($_POST["cognome"])."_".strtolower($_POST["nome"]).".png";
                        $path_qrcode = genera_file_path($path_qrcode, DIR_QR_CODE);

                        // Generazione del percorso di salvataggio del file della tessera associativa
                        $path_tessera = "tessera_".strtolower($_POST["cognome"])."_".strtolower($_POST["nome"]).".pdf";
                        $path_tessera = genera_file_path($path_tessera, DIR_CARTA_IDENTITA);

                        if($errore_file){
                            // Se c'è stato un errore viene comunicato tramite un messaggio
                            echo "<div class='error'>ERRORE: Non è stato possibile caricare uno o più file sul server. Riprovare più tardi.</div>";
                        }
                        else{
                            // Altrimenti viene creata l'istanza del nuovo socio ...
                            $socio = new Socio(
                                    1,
                                    strtolower($_POST["cognome"]),
                                    strtolower($_POST["nome"]),
                                    $_POST["data-nascita"],
                                    $_POST["sesso"],
                                    $_POST["altezza"],
                                    $_POST["professione"],
                                    $_POST["email"],
                                    $_POST["telefono"],
                                    $path_fototessera,
                                    $path_cartaidentita,
                                    $path_presentazione,
                                    $path_qrcode,
                                    $path_tessera,
                                    (new DateTime())->format("Y-m-d H:i:s"),
                                    STATO_REGISTRATO
                            );
                            // ... e viene aggiornata la tabella dei soci
                            if(aggiungi_socio($socio)){
                                // Se il nuovo socio è stato aggiunto correttamente viene stampato un messaggio di
                                // cortesia e il link per tornare alla pagina principale
                                echo "<div class='log'>La registrazione è andata a buon fine.<br>Attendi che un amministratore approvi la tua domanda di iscrizione.</div>";
                                echo "<a href='index.php'>Torna alla pagina principale</a>";
                            }
                            else{
                                echo "<div class='error'>Si è verificato un errore nell'inserimento dei dati.</div>";
                            }
                        }
                    }
                ?>
            </div>

            <div class="container">
                <h1>Riepilogo dei dati inseriti</h1>
                <table>
                    <tr class="form-table">
                        <th class="form-table">Cognome:</th>
                        <td class="form-table"><?php echo $_POST["cognome"] ?></td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table">Nome:</th>
                        <td class="form-table"><?php echo $_POST["nome"] ?></td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table">Data di nascita:</th>
                        <td class="form-table"><?php echo (new DateTime($_POST["data-nascita"]))->format("d/m/Y") ?></td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table">Sesso:</th>
                        <td class="form-table"><?php echo $_POST["sesso"] ?></td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table">Altezza:</th>
                        <td class="form-table"><?php echo $_POST["altezza"] ?> metri</td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table">Professione:</th>
                        <td class="form-table"><?php echo ucfirst($_POST["professione"]) ?></td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table">Email:</th>
                        <td class="form-table"><?php echo $_POST["email"] ?></td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table">Telefono:</th>
                        <td class="form-table"><?php echo $_POST["telefono"] ?></td>
                    </tr>
                    <tr class="form-table">
                        <th class="form-table"><label for="txtaPresentazione">Presentati:</label></th>
                        <td class="form-table">
                            <textarea name="presentazione" id="txtaPresentazione" rows="10" readonly><?php echo $_POST["presentazione"] ?></textarea>
                        </td>
                    </tr>
                </table>
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
