<?php
    /*
        FILE: associazione/login.php
        Contenuto: Pagina di accesso all'area riservata agli amministratori del sito
        AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
        ULTIMA MODIFICA: 12/12/2025
    */
    session_start();
    include("libs/funzioni.php");

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        // La pagina viene richiamata tramite post-back per elaborare i dati inseriti dall'utente
        $email = "";
        $password = "";

        if(!empty($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
            // L'email deve essere valorizzata e deve rispettare il formato standard di un'email
            $email = $_POST["email"];
        }
        else{
            echo "<div class='error'>ERRORE: Inserire una email valida.</div>";
        }

        if(!empty($_POST["password"])){
            // La password deve essere valorizzata
            $password = trim($_POST["password"]);
        }
        else{
            echo "<div class='error'>ERRORE: Inserire la password.</div>";
        }

        // Se entrambi i dati sono validi lo script esegue l'autenticazione dell'utente a mezzo delle sue credenziali
        if(!empty($email) && !empty($password)){
            $utente = verifica($email, $password);
            if(!empty($utente)){
                // Se la verifica ha successo viene creata la sessione e si viene reindirizzati alla pagina di amministrazione
                $_SESSION["logged_in"] = true;
                $_SESSION['email'] = $utente["email"];
                $_SESSION['utente'] = $utente["nome"];
                header("Location: adminer.php");
                exit;
            }
            else{
                // Altrimenti viene mostrato un messaggio di errore
                echo "<div class='warning'>ACCESSO NEGATO: Email o password errata.</div>";
            }
        }
    }
?>

<!DOCTYPE html>

<html lang="it">
    <head>
        <meta charset="utf-8">
        <link href="style.css" rel="stylesheet" type="text/css">
        <link href="resources/favicon.ico" rel="icon" type="image/vnd.microsoft.icon">
        <title>Accesso amministratori</title>
    </head>

    <body>
        <main>
            <div class="container">
                <a href="index.php">Torna alla pagina principale</a>
            </div>

            <div class="container">
                <h1>Pagina di accesso all'area di amministrazione</h1>
                <p>Inserire la propria email e la propria password per accedere.</p>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <table>
                        <tr class="form-table">
                            <th class="form-table"><label for="txtEmail">Email: </label></th>
                            <td class="form-table"><input type="email" id="txtEmail" name="email" required></td>
                        </tr>
                        <tr class="form-table">
                            <th class="form-table"><label for="txtPassword">Password: </label></th>
                            <td class="form-table"><input type="password" id="txtPassword" name="password" required></td>
                        </tr>
                    </table>
                    <input class="button-main" type="submit" id="btnAccedi" value="Accedi">
                </form>
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
