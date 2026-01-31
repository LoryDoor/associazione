CREATE DATABASE IF NOT EXISTS db_associazione;

use db_associazione;

DROP TABLE IF EXISTS Soci;
CREATE TABLE IF NOT EXISTS Soci (
    ID integer(4) PRIMARY KEY AUTO_INCREMENT,
    Cognome varchar(30) NOT NULL,
    Nome varchar(30) NOT NULL,
    DataNascita date NOT NULL,
    Sesso ENUM(
        'M',
        'F'
    ) NOT NULL,
    Altezza decimal(3,2) NOT NULL CHECK(Altezza > 0.80 AND Altezza < 2.20),
    Professione ENUM(
        'Impiegato',
        'Funzionario',
        'Libero professionista',
        'Studente',
        'Pensionato'
    ) NOT NULL,
    Email varchar(100),
    Telefono varchar(13),
    FileNameFototessera varchar(255) NOT NULL,
    FileNameCartaIdentita varchar(255) NOT NULL,
    Presentazione text NOT NULL,
    FileNameQRCode varchar(255),
    FileNameTessera varchar(255),
    DataOraRegistrazione datetime NOT NULL DEFAULT NOW(),
    Stato ENUM(
        'EFFETTIVO',
        'REGISTRATO'
    ) NOT NULL
);

DROP TABLE IF EXISTS Amministratori;
CREATE TABLE IF NOT EXISTS Amministratori (
    ID integer(3) PRIMARY KEY AUTO_INCREMENT,
    NomeUtente varchar(60) NOT NULL,
    Email varchar(100) NOT NULL UNIQUE,
    PasswordHash char(255) NOT NULL
);

INSERT INTO Amministratori (NomeUtente, Email, PasswordHash) VALUES
    ('LORENZO PORTA', 'lorenzo.porta@studenti.fauser.edu', SHA2('porta', 256)),
    ('DAVIDE BALDINU', 'davide.baldinu@studenti.fauser.edu', SHA2('baldinu', 256)),
    ('FRANCESCO CAMPANELLO', 'francesco.campanello@studenti.fauser.edu', SHA2('campanello', 256)),
    ('MARCO FERRARI', 'marco.ferrari@docenti.fauser.edu', SHA2('ferrari', 256)),
    ('PAOLA PIRRÒ', 'paola.pirro@docenti.fauser.edu', SHA2('pirro', 256));

DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS cerca_persona(
    IN pEmail varchar(100),
    IN pPassword varchar(100)
)
BEGIN
    SELECT Id, NomeUtente, Email FROM Amministratori WHERE Email = pEmail AND SHA2(pPassword, 256) = PasswordHash;
END $$

DELIMITER ;
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS rendi_effettivo(
    IN pID integer(4)
)
BEGIN
    UPDATE Soci SET Stato = 'EFFETTIVO' WHERE ID = pID;
END $$

DELIMITER ;
