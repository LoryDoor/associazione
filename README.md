# Associazione

Questo progetto implementa un sistema web per la gestione dei soci di un'associazione. Gli utenti possono registrarsi tramite un form online e gli amministratori possono gestire i soci tramite una sezione riservata.

## Funzionalità principali

### Registrazione Socio
I soci possono registrarsi tramite un form che richiede le seguenti informazioni:
- Cognome
- Nome
- Data di nascita
- Sesso
- Altezza (in metri)
- Professione (5 opzioni predefinite)
- Email (univoca)
- Numero telefonico
- Fototessera (formato JPG)
- Carta d’identità (formato PDF)
- Presentazione (testo libero)

**Validazione dei dati**:
- I dati vengono validati prima della registrazione.
- Errori di validazione (ad esempio, formato errato della data o email già registrata) vengono segnalati all’utente.

### Memorizzazione dei dati
I dati dei soci vengono memorizzati in un file di testo `soci.txt`. Ogni socio ha un codice univoco progressivo e un campo “stato” che può essere:
- **registrato**: il socio è registrato ma non ancora approvato dall'amministratore.
- **effettivo**: la registrazione è stata approvata dall'amministratore.

**File aggiuntivi**:
- Le fototessere e i documenti PDF vengono salvati in cartelle dedicate.
- Ogni presentazione viene memorizzata in un file di testo separato.

Tecniche di gestione dei file impediscono la sovrascrittura dei dati (foto, PDF, testi di presentazione).

### Sezione Pubblica
- **Iscrizione**: i nuovi soci possono registrarsi.
- **Elenco Soci Effettivi**: visualizzazione dell'elenco dei soci effettivi con informazioni pubbliche (codice socio, cognome, nome, età, fototessera, giorni dall’iscrizione).

### Sezione Riservata (Amministratori)
- **Gestione soci**: gli amministratori possono visualizzare e approvare i soci con stato “registrato” e renderli “effettivi”.
- **Visualizzazione dettagli**: gli amministratori possono visualizzare i dettagli completi di un socio, inclusa la carta d’identità.
- **Eliminazione soci**: è possibile eliminare un socio e rimuovere tutti i suoi documenti.
- **Generazione tessera associativa**: creazione della tessera associativa in formato PDF contenente:
  - Logo e nome dell’associazione
  - Codice socio, Cognome, Nome, Altezza, Età
  - Fototessera
  - QR Code che rimanda alla pagina del socio nella sezione pubblica

### Autenticazione Amministratori
Gli amministratori accedono alla sezione riservata tramite credenziali memorizzate in un file di testo `amministratori.txt` con password criptata (`password_hash`).

## Tecnologie utilizzate
- **FPDF**: Libreria PHP per la generazione della tessera associativa in formato PDF
- **PHP QR Code**: Libreria PHP per la generazione del QR Code
