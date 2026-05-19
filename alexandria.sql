-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping database structure for alexandria
CREATE DATABASE IF NOT EXISTS `alexandria` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `alexandria`;

-- Dumping structure for table alexandria.opera
CREATE TABLE IF NOT EXISTS `Opera` (
  `ISBN` varchar(17) NOT NULL,
  `Nome` varchar(50) NOT NULL,
  `Autore` varchar(40) NOT NULL,
  `Genere` varchar(20) NOT NULL,
  `Descrizione` varchar(2000) NOT NULL,
  `Copertina` varchar(100) NOT NULL DEFAULT 'default.jpg',
  `CasaEditrice` varchar(30) NOT NULL,
  `AnnoPubblicazione` int NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ISBN`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table alexandria.copialibro
CREATE TABLE IF NOT EXISTS `copiaLibro` (
  `idCopia` int NOT NULL AUTO_INCREMENT,
  `ISBN` varchar(17) NOT NULL,
  `Stato` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idCopia`) USING BTREE,
  KEY `ISBN` (`ISBN`) USING BTREE,
  CONSTRAINT `copiaLibro_ibfk_1` FOREIGN KEY (`ISBN`) REFERENCES `Opera` (`ISBN`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table alexandria.utente
CREATE TABLE IF NOT EXISTS `Utente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `Nome` varchar(30) NOT NULL,
  `Cognome` varchar(30) NOT NULL,
  `Password` varchar(60) NOT NULL,
  `Utenza` int NOT NULL DEFAULT '3',
  `propic` varchar(50) DEFAULT NULL,
  `prenotazioni` int DEFAULT NULL,
  `punteggio` int NOT NULL DEFAULT '100',
  PRIMARY KEY (`Email`),
  UNIQUE KEY `id` (`id`),
  KEY `Utenza` (`Utenza`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Trigger per aggiornare lo stato di un utente premium
DELIMITER //
CREATE TRIGGER aggiorna_stato_premium
BEFORE UPDATE ON Utente
FOR EACH ROW
BEGIN
    IF OLD.Utenza IN (3, 4) THEN
        IF NEW.punteggio >= 200 THEN
            SET NEW.Utenza = 3;
        ELSEIF NEW.punteggio < 200 THEN
            SET NEW.Utenza = 4;
        END IF;
    END IF;
END; //
DELIMITER ;

-- Data exporting was unselected.

-- Dumping structure for table alexandria.prenotazione
CREATE TABLE IF NOT EXISTS `Prenotazione` (
  `Email` varchar(100) NOT NULL,
  `idPrenotazione` int NOT NULL AUTO_INCREMENT,
  `idCopia` int NOT NULL,
  `InizioPrenotazione` date NOT NULL,
  `FinePrenotazione` date NOT NULL,
  `FinePrestito` date DEFAULT NULL,
  `InizioPrestito` date DEFAULT NULL,
  `FineAttesa` date DEFAULT NULL,
  PRIMARY KEY (`idPrenotazione`),
  KEY `Email` (`Email`,`idCopia`),
  KEY `Prenotazione_ibfk_2` (`idCopia`),
  CONSTRAINT `Prenotazione_ibfk_1` FOREIGN KEY (`Email`) REFERENCES `Utente` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Prenotazione_ibfk_2` FOREIGN KEY (`idCopia`) REFERENCES `copiaLibro` (`idCopia`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table alexandria.segnalazione
CREATE TABLE IF NOT EXISTS `Segnalazione` (
  `idSegnalazione` int NOT NULL AUTO_INCREMENT,
  `userEmail` varchar(100) NOT NULL,
  `Oggetto` varchar(50) NOT NULL,
  `Messaggio` text NOT NULL,
  `imgSegn` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idSegnalazione`),
  CONSTRAINT `Segnalazione_ibfk_1` FOREIGN KEY (`userEmail`) REFERENCES `Utente` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table alexandria.recensione
CREATE TABLE IF NOT EXISTS `recensione` (
    `id` int NOT NULL AUTO_INCREMENT,
    `userEmail` VARCHAR(100) NOT NULL,
    `idOpera` int NOT NULL,
    `Titolo` VARCHAR(50) NOT NULL,
    `Messaggio` text NOT NULL,
    `Voto` TINYINT NOT NULL,
    `data_creazione` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_utente_libro` (`userEmail`, `idOpera`),
    CONSTRAINT `recensione_chk_1` CHECK (`Voto` >= 1 AND `Voto` <= 5),
    CONSTRAINT `fk_recensione_opera` FOREIGN KEY (`idOpera`) REFERENCES `Opera` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_recensione_utente` FOREIGN KEY (`userEmail`) REFERENCES `Utente` (`Email`) ON DELETE CASCADE ON UPDATE CASCADE    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `notifiche` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `utente_id` INT NOT NULL,
    `titolo` VARCHAR(255) NOT NULL,
    `messaggio` TEXT NOT NULL,
    `letta` TINYINT(1) DEFAULT 0,
    `data_creazione` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `url_azione` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`utente_id`) REFERENCES `Utente`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
INSERT INTO `Opera` (`ISBN`, `Nome`, `Autore`, `Genere`, `Descrizione`, `Copertina`, `CasaEditrice`, `AnnoPubblicazione`, `id`) VALUES
('1331979821020', 'Dummy', 'Dummy', 'Umoristico', 'DummyDummyDummy', 'default.jpg', 'Dummy', 1984, 1),
('1613154538417', 'Dummy', 'Dummy', 'Umoristico', 'DummyDummyDummy', 'default.jpg', 'Dummy', 1984, 2),
('1885093621774', 'Dummy', 'Dummy', 'Umoristico', 'DummyDummyDummy', 'default.jpg', 'Dummy', 1984, 3),


INSERT INTO `copiaLibro` (`idCopia`, `ISBN`, `Stato`) VALUES
(1, '1331979821020', 1),
(2, '1331979821020', 1),
(3, '1331979821020', 1),
(4, '1613154538417', 1),
(5, '1613154538417', 1),
(6, '1613154538417', 1),
(7, '1885093621774', 1),
(8, '1885093621774', 1),
