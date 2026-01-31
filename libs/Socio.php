<?php
/*
    FILE: associazione/libs/Socio.php
    CONTENUTO: Classe Socio per lavorare agilmente i dati del record di memorizzazione dei soci
    AUTORE: Lorenzo Porta - 5FIN - ITT "G. Fauser" di Novara
    ULTIMA MODIFICA: 30/01/2026
*/

namespace libs;
use DateTime;

class Socio
{
	private $codice_socio;
    private $cognome;
    private $nome;
    private $data_nascita;
    private $sesso;
    private $altezza;
    private $professione;
    private $email;
    private $telefono;
    private $fileName_fototessera;
    private $fileName_cartaIdentita;
    private $fileName_presentazione;
    private $fileName_qrcode;
    private $fileName_tessera;
    private $data_ora_iscrizione;
    private $stato;

    public function __construct(
        int $codice_socio, string $cognome, string $nome, string $data_nascita, string $sesso, float $altezza,
        string $professione, string $email, string $telefono, string $path_fototessera, string $path_cartaidentita,
        string $path_presentazione, string $path_qrcode, string $path_tessera, string $data_ora_iscrizione,
        string $stato
    )
    {
		$this->codice_socio = $codice_socio;
        $this->cognome = $cognome;
        $this->nome = $nome;
        $this->data_nascita = new DateTime($data_nascita);
        $this->sesso = $sesso;
        $this->altezza = $altezza;
        $this->professione = $professione;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->fileName_fototessera = basename($path_fototessera);
        $this->fileName_cartaIdentita = basename($path_cartaidentita);
        $this->fileName_presentazione = basename($path_presentazione);
        $this->fileName_qrcode = basename($path_qrcode);
        $this->fileName_tessera = basename($path_tessera);
        $this->data_ora_iscrizione = new DateTime($data_ora_iscrizione);
        $this->stato = $stato;
    }

    // Crea un'istanza della classe socio partendo da una tupla del database
    public static function crea_da_result_set(array $result) : Socio
    {
        return new Socio($result[0], $result[1], $result[2], $result[3], $result[4], $result[5],  $result[6],
            $result[7], $result[8], $result[9], $result[10], $result[11], $result[12], $result[13], $result[14],
            $result[15]);
    }

    // Metodi getter
    public function getCodiceSocio(): string
    {
        return $this->codice_socio;
    }

    public function getCognome(): string
    {
        return $this->cognome;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getDataNascita(): DateTime
    {
        return $this->data_nascita;
    }

    public function getEta() : int
    {
        $oggi = new DateTime();
        $differenza = $this->data_nascita->diff($oggi);
        return $differenza->y;
    }

    public function getSesso(): string
    {
        return $this->sesso;
    }

    public function getAltezza(): float
    {
        return $this->altezza;
    }

    public function getProfessione(): string
    {
        return $this->professione;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function getFileNameFototessera(): string
    {
        return $this->fileName_fototessera;
    }

    public function getFileNameCartaidentita(): string
    {
        return $this->fileName_cartaIdentita;
    }

    public function getFileNamePresentazione(): string
    {
        return $this->fileName_presentazione;
    }

    public function getFileNameQrcode(): string{
        return $this->fileName_qrcode;
    }

    public function getFileNameTessera(): string
    {
        return $this->fileName_tessera;
    }

    public function getDataOraIscrizione(): DateTime
    {
        return $this->data_ora_iscrizione;
    }

    public function getStato(): string{
        return $this->stato;
    }

    // Metodi setter
    public function setStato(string $stato){
        $this->stato = $stato;
    }

    // Metodo di conversione in stringa stante alla struttura del record del file dei soci
    public function toString(): string
    {
        return
            "$this->codice_socio;$this->cognome;$this->nome;" .
            $this->data_nascita->format("Y-m-d") .
            ";$this->sesso;$this->altezza;$this->professione;$this->email;$this->telefono;" .
            "$this->fileName_fototessera;$this->fileName_cartaIdentita;$this->fileName_presentazione;$this->fileName_qrcode;$this->fileName_tessera;" .
            $this->data_ora_iscrizione->format("Y-m-d H:i:s") . ";$this->stato\n";
    }

    public function toInsertRecord() : string
    {
        return
            "('$this->cognome'," .
            "'$this->nome'," .
            "'" . $this->data_nascita->format("Y-m-d") . "'," .
            "'$this->sesso'," .
            "$this->altezza," .
            "'$this->professione'," .
            "'$this->email'," .
            "'$this->telefono'," .
            "'$this->fileName_fototessera'," .
            "'$this->fileName_cartaIdentita'," .
            "'$this->fileName_presentazione'," .
            "'$this->fileName_qrcode'," .
            "'$this->fileName_tessera'," .
            "'" . $this->data_ora_iscrizione->format("Y-m-d H:i:s") . "'," .
            "'$this->stato')";
    }

    public function toArray() : array
    {
        return [
            $this->cognome, $this->nome, $this->data_nascita->format("Y-m-d"),
            $this->sesso, $this->altezza, $this->professione, $this->email, $this->telefono,
            $this->fileName_fototessera, $this->fileName_cartaIdentita, $this->fileName_presentazione,
            $this->fileName_qrcode, $this->fileName_tessera, $this->data_ora_iscrizione->format("Y-m-d H:i:s"),
            $this->stato
        ];
    }
}
