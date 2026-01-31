<?php
/*
    FILE: associazione/libs/tessera.php
    Contenuto: Libreria ad-hoc per la creazione della tessera associativa e del relativo QR code
    AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
    ULTIMA MODIFICA: 31/01/2026
*/

require_once "funzioni.php";
require_once "phpqrcode/phpqrcode.php"; // Libreria per la generazione di codici QR
require_once "fpdf/fpdf.php"; // Libreria per la creazione di file in formato PDF tramite script
use libs\Socio;

// Costanti specifiche per la tessera
const NOME_ASSOCIAZIONE = "Regno Carlino";
const PATH_LOGO = "resources/logo.jpg";
const PATH_FILIGRANA = "resources/filigrana.png";
const URL_BASE = "https://labs3.fauser.edu/~web12778/associazione_db/informazioni-socio.php?id=";
const CARD_WIDTH = 85.6; // mm
const CARD_HEIGHT = 53.98; // mm
const COLORE_SFONDO = array(255, 213, 228); // Rosa pastello
const COLORE_PRIMARIO = array(195, 50, 117); // Magenta

// Genera un codice QR con il URL passato come parametro in un file in formato PNG e ne restituisce il percorso
function crea_codice_qr(string $url) : string
{
    $query_params = null;
    parse_str(parse_url($url)['query'], $query_params); // Estrae la query string dal URL e la salva nella variabile
    $socio = cerca_socio($query_params["id"]); // Estraggo l'id dai parametri della query string e lo passo alla funzione
    $file_path = DIR_QR_CODE . $socio->getFileNameQrcode(); // Percorso univoco generato per quel socio
    QRcode::png($url, $file_path, "L", 10, 2);
    return $file_path;
}

function crea_tessera(Socio $socio){
    // Dati del socio per cui verrà creata la tessera
    $codice = str_pad($socio->getCodiceSocio(), 4, "0", STR_PAD_LEFT);
    $nome = ucwords($socio->getNome());
    $cognome = ucwords($socio->getCognome());
    $altezza = $socio->getAltezza();
    $eta = $socio->getEta();
    $foto = $socio->getFileNameFototessera();

    // Crea PDF
    // Orientamento: landscape; Unità di misura: millimetri; Dimensioni: Larghezza: 2*85,6mm, Altezza: 53,98mm
    $pdf = new FPDF('L', 'mm', array(CARD_WIDTH * 2, CARD_HEIGHT));
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();

    // =============================================== FRONTE (SINISTRA) ===============================================
    $fronteX = 0;

    // Sfondo
    $pdf->SetFillColor(COLORE_SFONDO[0], COLORE_SFONDO[1], COLORE_SFONDO[2]);
    $pdf->Rect($fronteX, 0, CARD_WIDTH, CARD_HEIGHT, 'F');

    // Bordo della faccia
    $pdf->SetDrawColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetLineWidth(1);
    $pdf->Rect($fronteX, 0, CARD_WIDTH, CARD_HEIGHT);

    // Logo (7.5mm x 7.5mm)
    $logoSize = 7.5;
    $logoX = $fronteX + 3;
    $logoY = 3;
    $pdf->Image(PATH_LOGO, $logoX, $logoY, $logoSize, $logoSize);

    // Nome associazione a destra del logo
    $pdf->SetFont('Times', 'B', 14);
    $pdf->SetTextColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetXY($logoX + $logoSize + 2, $logoY);
    $pdf->Cell(0, 5, NOME_ASSOCIAZIONE, 0, 1);

    // Codice socio in alto a destra
    $pdf->SetFont('Times', 'B', 14);
    $pdf->SetTextColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetXY(CARD_WIDTH-21, $logoY);
    $pdf->Cell(8, 5, 'Socio:', 0, 0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(CARD_WIDTH-14, $logoY);
    $pdf->Cell(18, 5, $codice, 0, 0, 'L');

    // Fototessera (24.4mm x 30mm)
    $fotoWidth = 24.4;
    $fotoHeight = 30;
    $fotoX = $fronteX + 3;
    $fotoY = $logoY + $logoSize + 5; // 5mm sotto il logo
    $percorso_foto = DIR_FOTOTESSERA . $foto;

    // Bordo della foto
    $pdf->SetDrawColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetLineWidth(0.7);
    $pdf->Rect($fotoX, $fotoY, $fotoWidth, $fotoHeight);

    // Diamo per scontato che la foto esista
    $pdf->Image($percorso_foto, $fotoX, $fotoY, $fotoWidth, $fotoHeight);

    // Dati socio a destra della foto (1mm di distanza)
    $datiX = $fotoX + $fotoWidth + 1;
    $datiY = $fotoY;

    $etichettaWidth = 17; // Larghezza per le etichette
    $valoreWidth = 17;    // Larghezza per i valori

    // Nome
    $pdf->SetFont('Times', 'B', 10);
    $pdf->SetTextColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetXY($datiX, $datiY);
    $pdf->Cell($etichettaWidth, 5, 'Nome:', 0,0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($datiX + $etichettaWidth);
    $pdf->Cell($valoreWidth, 5, $nome, 0, 1, 'L');

    // Cognome
    $pdf->SetTextColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetXY($datiX, $datiY + 6);
    $pdf->Cell($etichettaWidth, 5, 'Cognome:',0,0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($datiX + $etichettaWidth);
    $pdf->Cell($valoreWidth, 5, $cognome, 0, 1, 'L');

    // Altezza
    $pdf->SetTextColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetXY($datiX, $datiY + 12);
    $pdf->Cell($etichettaWidth, 5, 'Altezza:', 0,0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($datiX + $etichettaWidth);
    $pdf->Cell($valoreWidth, 5, $altezza . ' m', 0, 1, 'L');

    // Età
    $pdf->SetTextColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetXY($datiX, $datiY + 18);
    $pdf->Cell($etichettaWidth, 5, iconv('UTF-8', 'windows-1252', 'Età:'), 0,0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($datiX + $etichettaWidth);
    $pdf->Cell($valoreWidth, 5, $eta . ' anni', 0, 1, 'L');

    // QR Code (20mm x 20mm)
    $qrSize = 20;
    $qrX = $fronteX + 62.2; // 6.22cm dal bordo sinistro
    $qrY = 30.8; // 3.08cm dal bordo superiore

    // Genera il QR code
    $url = URL_BASE . $socio->getCodiceSocio();
    $path_qrcode = crea_codice_qr($url);

    // Eseguiamo il controllo qualora non sia stato possibile generare il QR code
    if ($path_qrcode && file_exists($path_qrcode)) {
        $pdf->Image($path_qrcode, $qrX, $qrY, $qrSize, $qrSize);
    } else {
        // Se non esiste il QR code viene sostituito da un placeholder
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->SetLineWidth(0.3);
        $pdf->Rect($qrX, $qrY, $qrSize, $qrSize);
        $pdf->SetFont('Times', '', 6);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($qrX, $qrY + $qrSize/2 - 2);
        $pdf->Cell($qrSize, 4, 'QR CODE', 0, 0, 'C');
    }

    // ================================================ RETRO (DESTRA) ================================================
    $retroX = CARD_WIDTH; // Metà pagina

    // Sfondo
    $pdf->SetFillColor(COLORE_SFONDO[0], COLORE_SFONDO[1], COLORE_SFONDO[2]);
    $pdf->Rect($retroX, 0, CARD_WIDTH, CARD_HEIGHT, 'F');

    // Bordo della faccia
    $pdf->SetDrawColor(COLORE_PRIMARIO[0], COLORE_PRIMARIO[1], COLORE_PRIMARIO[2]);
    $pdf->SetLineWidth(1);
    $pdf->Rect($retroX, 0, CARD_WIDTH, CARD_HEIGHT);

    // Logo centrale grande (45mm x 45mm)
    $logoGrandeSize = 45;
    $logoGrandeX = $retroX + (CARD_WIDTH - $logoGrandeSize) / 2;
    $logoGrandeY = (CARD_HEIGHT - $logoGrandeSize) / 2;
    $pdf->Image(PATH_LOGO, $logoGrandeX, $logoGrandeY, $logoGrandeSize, $logoGrandeSize);

    // Filigrana (File: associazione/resources/filigrana.png)
    $colonnaWidth = 8;   // mm di larghezza
    $colonnaHeight = 40; // mm di altezza
    // Posizione centrale verticale
    $colonnaY = (CARD_HEIGHT - $colonnaHeight) / 2;
    // Colonna SINISTRA (3mm dal bordo sinistro del retro)
    $pdf->Image(PATH_FILIGRANA, $retroX + 3, $colonnaY, $colonnaWidth, $colonnaHeight);
    // Colonna DESTRA (3mm dal bordo destro del retro)
    $pdf->Image(PATH_FILIGRANA, $retroX + CARD_WIDTH - $colonnaWidth - 3, $colonnaY, $colonnaWidth, $colonnaHeight);

    // Produzione del file
    $pdf->Output('F', DIR_TESSERA . $socio->getFileNameTessera());
}
