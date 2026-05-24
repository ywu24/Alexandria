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
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table alexandria.copialibro
CREATE TABLE IF NOT EXISTS `copiaLibro` (
  `idCopia` int NOT NULL AUTO_INCREMENT,
  `ISBN` varchar(17) NOT NULL,
  `Stato` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idCopia`) USING BTREE,
  KEY `ISBN` (`ISBN`) USING BTREE,
  CONSTRAINT `copiaLibro_ibfk_1` FOREIGN KEY (`ISBN`) REFERENCES `Opera` (`ISBN`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=549 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `notifiche` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `utente_id` INT NOT NULL,
    `titolo` VARCHAR(255) NOT NULL,
    `messaggio` TEXT NOT NULL,
    `letta` TINYINT(1) DEFAULT 0,
    `data_creazione` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `url_azione` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`utente_id`) REFERENCES `Utente`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
INSERT INTO `Opera` (`ISBN`, `Nome`, `Autore`, `Genere`, `Descrizione`, `Copertina`, `CasaEditrice`, `AnnoPubblicazione`, `id`) VALUES
('012378432', 'Piccoli Suicidi tra Amici', 'Arto Paasilinna', 'Umoristico', 'Si può ridere della morte? Ovvio che sì, anzi forse è l’argomento principe su cui valga la pena ridere. Qui abbiamo due disperati che decidono di togliersi la vita, caso vuole che scelgano lo stesso posto per farlo. Il reciproco imbarazzo li costringe a rimandare il proposito. Ne nasce un\'amicizia, poi una carbonara associazione per aspiranti suicidi, e una corriera porterà i suoi depressi membri in giro per l\'Europa in cerca della miglior rupe da cui gettarsi. L\'opera del più conosciuto tra gli scrittori finlandesi ci trascina in una picaresca corsa verso una morte ricca di vero divertimento.', 'PiccoliSuiciditraAmici6a0c20d1133f7.jpeg', 'Mondadori', 1990, 116),
('10234782', 'Una Visita Guidata', 'Alan Bennett', 'Umoristico', 'Non è un romanzo, ma una visita guidata alla National Gallery di Londra. Però se vi aspettate una paludata lezione sull’arte avete sbagliato indirizzo. Alan Bennett è il cicerone che tutti sognano. Una passeggiata tra le sale del famoso museo per scoprirne la bellezza attraverso l’ironia dell’autore e l’inaspettata comicità di alcune scelte pittoriche. Quando ridere si dimostra una raffinata forma del pensiero.', 'UnaVisitaGuidata6a0c210648e4a.jpeg', 'Mondadori', 2001, 117),
('1025348', 'E pensare che c\'era Giorgio Gaber', 'Andrea SCanzi', 'Biografia', 'Il libro celebra il talento e l\'umanità di Giorgio Gaber, uno dei più grandi pensatori italiani del Novecento, vent\'anni dopo la sua scomparsa. Andrea Scanzi racconta Gaber attraverso la pièce teatrale voluta dalla Fondazione Gaber, riproposta in oltre duecento rappresentazioni in tutta Italia. La prima parte del libro è il testo del suo spettacolo, una storia appassionata della carriera del Signor G. La seconda parte offre un\'antologia di pensieri e parole di intellettuali, artisti e appassionati famosi che hanno scritto per l\'occasione. Il volume fornisce un meraviglioso approccio al corpus artistico di Gaber, celebre per le sue opere indimenticabili del Teatro Canzone', 'Epensarechec\'eraGiorgioGaber6a0c1a18772ae.jpeg', 'Mondadori', 2023, 99),
('1029384675', 'IT', 'Stephen King', 'Horror', 'Per i pochi che non l’avessero già letto: per questo romanzo dell’orrore bisogna immaginarsi quelle figure che possono venire a trovarci nel cuore della notte, nel peggiore dei nostri incubi (e che ci fanno battere i denti e svegliare in allarme con il cuore a duecento battiti!). Come un mostro con la faccia da clown, come quelli – innocui – che animano le feste per bambini. Scritto nel 1986 dal gran maestro del brivido Stephen King “It” è una pietra miliare, un romanzo-capolavoro che accede proprio alle profondità dell’umano, alle menzogne e alle fragilità, e le trasforma in racconto, in saga, in narrazione epica. Meglio, però, lasciare una lucina accesa prima di andare a dormire.', 'IT6a0c1dd9b697a.jpeg', 'Mondadori', 1986, 109),
('1029834783', 'Il Mondo Nuovo', 'Aldous Huxley', 'Distopia', 'La produzione in serie è applicata anche alle nascite nel nuovo mondo, appena liberatosi dopo una guerra decennale. La comunità è divisa in caste, le famiglie sono ormai superate e fin dal concepimento ogni individuo è sottoposto a un condizionamento psicofisico.\r\n(Mondadori, traduzione di Lorenzo Gigli e Luciano Bianciardi)', 'IlMondoNuovo6a0c226fd3227.jpg', 'Mondadori', 1932, 120),
('10923864', 'Dieci Piccoli Indiani', 'Agatha Christie', 'Giallo', 'Dieci persone estranee l\'una all\'altra sono state invitate a soggiornare in una splendida villa su un\'isola, senza sapere il nome del generoso ospite. Eppure, chi per curiosità, chi per bisogno, chi per opportunità, hanno accettato l\'invito. Ma non c\'è il padrone di casa ad aspettarli. Trovano invece una poesia incorniciata e appesa sopra il caminetto di ciascuna camera. E una voce inumana e penetrante che li accusa di essere tutti assassini. Una trappola spietata e geniale, per gli ospiti e per i lettori.', 'DieciPiccoliIndiani6a0c181150100.jpg', 'Mondadori', 1939, 95),
('10981234', 'Michele Ferrero', 'Salvatore Giannella', 'Biografia', 'Il libro presenta la prima biografia dell\'inventore della Nutella, Michele Ferrero, figura simbolo dell\'eccellenza italiana. Descrive le sue intuizioni geniali, visione internazionale e attenzione alla qualità dei prodotti e dei dipendenti. Un uomo capace di tramandare l\'azienda di famiglia, fondata dai genitori, fino a farla diventare una delle aziende più importanti e amate a livello globale. Nonostante la riservatezza, l\'autore Giannella ha intervistato numerose persone che lo hanno conosciuto da vicino, rivelandone la sua umiltà e il modo di fare impresa incentrato sulla persona e sui valori umani. Un ritratto entusiasmante di un imprenditore che ha segnato la storia italiana e internazionale.', 'MicheleFerrero6a0c19c9a8e10.jpeg', 'Mondadori', 2017, 98),
('109871234', 'Anna', 'Niccolò Ammaniti', 'Distopia', 'In una Sicilia diventata un\'immensa rovina, una tredicenne cocciuta e coraggiosa parte alla ricerca del fratellino rapito. Fra campi arsi e boschi misteriosi, ruderi di centri commerciali e città abbandonate, fra i grandi spazi deserti di un\'isola riconquistata dalla natura e selvagge comunità di sopravvissuti, Anna ha come guida il quaderno che le ha lasciato la mamma con le istruzioni per farcela. E giorno dopo giorno scopre che le regole del passato non valgono più, dovrà inventarne di nuove. Con \"Anna\" Niccolò Ammaniti ha scritto il suo romanzo più struggente. Una luce che si accende nel buio e allarga il suo raggio per rivelare le incertezze, gli slanci del cuore e la potenza incontrollabile della vita. Perché, come scopre Anna, la \"vita non ci appartiene, ci attraversa\".', 'Anna6a0c1ca35a8f3.jpeg', 'Mondadori', 2018, 106),
('1234', 'Omaggio alla Catalogna', 'George Orwell', 'Romanzo Storico', '1984 (titolo orig. Nineteen Eighty-Four, anche pubblicato come 1984) è un romanzo distopico di fantapolitica, oltreché racconto morale, dello scrittore inglese George Orwell, pubblicato l\'8 giugno 1949.\r\n\r\nUltima opera da lui completata e quinto romanzo, è incentrato sulle conseguenze del totalitarismo, sulla sorveglianza di massa, sulla repressione delle libertà e l\'irreggimentazione del popolo e dei comportamenti all\'interno della società. Orwell, un socialista democratico, modellò lo Stato autoritario del romanzo sia sull\'Unione Sovietica di Stalin sia sulla Germania nazista di Hitler. Ancora più in generale, il romanzo analizza il ruolo della verità e dei fatti dentro alle società e degli astuti sistemi nei quali essi possano essere manipolati.', 'OmaggioallaCatalogna69f2035482e74.jpg', 'Oscar', 1939, 72),
('123456', 'Io Non Ho Paura', 'Niccolò Ammaniti', 'Horror', 'Da un pozzo in campagna, nel Sud Italia a fine anni Settanta, in cui cade il piccolo Michele Amitrano nasce una storia potente, cupa, misteriosa e soffocante. In questo romanzo Niccolò Ammaniti va al cuore della sua narrativa, con una storia tesa e dal ritmo serrato, un congegno a orologeria che si carica fino a una conclusione sorprendente: e mette in scena la paura stessa. Il risultato è un racconto potente e di assoluta felicità narrativa, dove si respirano atmosfere che vanno da Clive Barker alle “Avventure di Tom Sawyer”, alle “Fiabe italiane” di Calvino.', 'IoNonHoPaura6a0c1d99c654e.jpeg', 'Mondadori', 2001, 108),
('12347647', 'Cecità', 'José Saramago', 'Romanzo Storico', 'Cecità (titolo originale, in portoghese: Ensaio sobre a Cegueira, letteralmente \"Saggio sulla cecità\") è un romanzo dello scrittore e premio Nobel per la letteratura portoghese José Saramago, pubblicato nel 1995.[1] In Italia, il titolo è stato tradotto eliminando parte di quello in lingua originale per esigenze editoriali; si è ritenuto infatti che Saggio sulla cecità avrebbe scoraggiato i lettori.\r\n\r\nIn un romanzo successivo di Saramago, Saggio sulla lucidità, si ritrovano personaggi presenti in Cecità. I fatti raccontati nei due romanzi sono legati, al punto che Saggio sulla lucidità può essere considerato come il \"seguito\" di Cecità.', 'Cecità6a0c13b11bac3.jpg', 'Oscar', 1995, 87),
('12348765', 'Moby Dick', 'Herman Melville', 'Azione', 'Un uomo e un mostruoso cetaceo si fronteggiano: è il conflitto più aspro, accanito e solitario mai immaginato, è la storia di ogni anima che si spinga a guardare oltre l\'abisso. Moby Dick è un gigantesco capodoglio, candida fonte di orrore e meraviglia; Achab è un capitano che, ossessionato da follia vendicatrice, lo insegue fino all\'ultimo respiro; Ismaele, un marinaio dall\'oscuro passato imbarcato sulla baleniera Pequod, è il narratore e, forse, l\'eroe della tragedia. Sullo sfondo, il ribollire sordo e terribile dell\'oceano, il vociare cosmopolita dell\'equipaggio, le descrizioni anatomiche delle balene e i puntuali resoconti di caccia. Così, pagina dopo pagina, i personaggi del dramma diventano i protagonisti di una nuova epica, con il fascino ambiguo e controverso di un destino contemporaneo. Con un saggio di Harold Bloom.', 'MobyDick6a0c1ea68d2ed.jpeg', 'Mondadori', 1959, 111),
('1234987', 'Io sono Malala', 'Io sono Malala.', 'Biografia', 'Gli estremisti religiosi in Pakistan hanno sparato alla testa di Malala per un solo motivo: in quanto donna e giovane rivendicava il diritto alla lettura e allo studio. È questo il motivo per cui la giovane Malala è stata universalmente riconosciuta come simbolo delle donne che combattono strenuamente ma senza armi per i loro ideali e per la loro libertà. Malala è anche la più giovane Premio Nobel per la pace di sempre. Le pagine di questo raccontano il coraggio dimostrato durante tutta la sua vita e rappresentano un inno alla tolleranza e al diritto all\'educazione di tutti i bambini. Questa biografia è il racconto appassionato di una giovane voce che prova a rivoluzionare il mondo.', 'IosonoMalala6a0c1b04e7003.jpeg', 'Mondadori', 2014, 102),
('1331979821020', 'La Guerra dei Mondi', 'H.G. WELLS', 'Fantascienza', 'Indice\r\n\r\n    Inizio\r\n    Trama\r\n    Interpretazioni\r\n    Adattamenti\r\n    Edizioni\r\n        In italiano\r\n    Note\r\n    Bibliografia\r\n    Voci correlate\r\n    Altri progetti\r\n    Collegamenti esterni\r\n\r\nLa guerra dei mondi (romanzo)\r\n\r\n    Voce\r\n    Discussione\r\n\r\n    Leggi\r\n    Modifica\r\n    Modifica wikitesto\r\n    Cronologia\r\n\r\nStrumenti\r\n\r\nAspetto\r\nTesto\r\n\r\n    Piccolo\r\n    Standard\r\n    Grande\r\n\r\nLarghezza\r\n\r\n    Standard\r\n    Largo\r\n\r\nColore (beta)\r\n\r\n    Automatico\r\n    Chiaro\r\n    Scuro\r\n\r\nLa guerra dei mondi\r\nTitolo originale	The War of the Worlds\r\nAltri titoli	Il terrore viene da Marte\r\nCopertina della prima edizione.\r\nAutore	H. G. Wells\r\n1ª ed. originale	1898\r\n1ª ed. italiana	1901\r\nGenere	romanzo\r\nSottogenere	fantascienza, horror\r\nLingua originale	inglese\r\nPersonaggi	\r\n\r\n    Narratore\r\n    fratello del narratore\r\n    Ogilvy: noto astronomo\r\n    Denning: scienziato esperto di meteoriti\r\n    Henderson: giornalista\r\n    Curato\r\n    Artigliere\r\n\r\nModifica dati su Wikidata · Manuale\r\n\r\nLa guerra dei mondi (The War of the Worlds) è un romanzo di H. G. Wells pubblicato originariamente a Londra in 9 puntate, da aprile a dicembre del 1897, sul Pearson\'s Magazine[1][2][3] e riproposto in contemporanea su Cosmopolitan.[4][5] È considerato come uno dei primi romanzi del genere fantascientifico ed è probabilmente rimasta l\'opera più famosa di Wells.\r\n\r\nLa guerra dei mondi venne adattato da Orson Welles in un celebre programma radiofonico nel 1938. La storia, narrata in forma di cronaca, venne interpretata in modo così realistico che una parte del popolo statunitense credette realmente che stesse avvenendo un\'invasione di extraterrestri, rimanendone scossa e turbata.\r\n\r\nLa prima edizione in italiano risale al 1901 edita da Francesco Vallardi', 'guerra.jpg', 'La Feltrinelli', 1984, 59),
('1613154538417', '1984', 'George Orwell', 'Distopia', '984 (titolo orig. Nineteen Eighty-Four, anche pubblicato come 1984) è un romanzo distopico di fantapolitica, oltreché racconto morale, dello scrittore inglese ...', 'default.jpg', 'Oscard', 1938, 50),
('1876512', 'Lungo cammino verso la libertà. Autobiografia', 'Nelson Mandela', 'Biografia', 'La straordinaria vita di Nelson Mandela si dipana dalle tranquille campagne del Transkei alle vivaci township di Johannesburg. Attraverso la sua militanza nell\'ANC e il coraggioso percorso attraverso ventisette anni di prigionia, dalla sua biografia emergono la lotta instancabile per la libertà politica e la dignità umana. Il Premio Nobel per la pace e la presidenza del Sudafrica sono il frutto di un lungo cammino verso la libertà. La tenacia e la dedizione di Mandela sono fonte d\'ispirazione, caratteristiche capaci di plasmare la storia del suo paese e il cuore di molti. Un ritratto affascinante dell\'uomo che ha cambiato il corso della storia.', 'Lungocamminoversolalibertà.Autobiografia6a0c1a5a66e26.jpeg', 'Mondadori', 1995, 100),
('1876534', 'La Materia Del Cosmo', 'Liu Cixin', 'Fantascienza', 'La materia del cosmo (黑暗森林S, Hēi\'àn sēnlínP, lett. \"La foresta oscura\") è un romanzo di fantascienza scritto nel 2008 dall\'autore cinese Liu Cixin.\r\nÈ il secondo romanzo della trilogia Memoria del passato della Terra (地球往事S, Dìqiú wǎngshìP), ma i lettori cinesi di solito si riferiscono alla serie usando il titolo del primo romanzo. La trilogia inizia con il romanzo vincitore del Premio Hugo Il problema dei tre corpi (三體T, 三体S, Sān tǐP, lett. \"Tre corpi\") e termina con Nella quarta dimensione (死神永生S, Sǐshén yǒngshēngP, lett. \"Morte immortale\")\r\nIl titolo originale dell\'opera deriva dall\'ipotesi della foresta oscura, coniata da Liu nel romanzo, ma descritta dall\'astronomo e autore David Brin già nel 1983 come possibile soluzione al paradosso di Fermi.', 'LaMateriaDelCosmo6a0c1fb408844.jpg', 'Mondadori', 2001, 113),
('198723', 'Il Problema Dei Tre Corpi', 'Liu Cixin', 'Fantascienza', 'Il problema dei tre corpi (三體T, 三体S, Sān tǐP, lett. \"Tre corpi\")[1][2] è un romanzo di fantascienza scritto nel 2006 dall\'autore cinese Liu Cixin.\r\n\r\nÈ il primo romanzo della trilogia Memoria del passato della Terra (地球往事S, Dìqiú wǎngshìP), ma i lettori cinesi di solito si riferiscono alla serie usando il titolo del primo romanzo.[1] Il secondo e il terzo romanzo della trilogia si intitolano rispettivamente La materia del cosmo (黑暗森林S, Hei\'an SenlinP, lett. \"La foresta oscura\").[2] e Nella quarta dimensione (死神永生S, Sǐshén yǒngshēngP, lett. \"Morte immortale\").[2]\r\n\r\nLa serie descrive un passato, un presente e un futuro immaginari nei quali, nel primo libro, la Terra entra in contatto con una civiltà aliena di un vicino sistema stellare composto da tre stelle simili al Sole che orbitano l\'una intorno all\'altra in un instabile sistema a tre corpi.\r\n\r\nIl titolo fa riferimento al problema dei tre corpi in astrodinamica.', 'IlProblemaDeiTreCorpi6a0c1ffbbf802.jpg', 'Mondadori', 2006, 114),
('21089236', 'Trappola Per Topi', 'Agatha Christie', 'Giallo', 'Il dramma si svolge nella pensione familiare Monkswell Manor, nella campagna inglese. Mollie e Giles Ralston ricevono i loro primi cinque ospiti. Ma è in corso una fittissima bufera di neve. La sera stessa la radio trasmette la notizia di un omicidio avvenuto a Paddington, la cui vittima è un\'anziana donna.\r\n\r\nNel frattempo nell\'albergo arrivano degli strani clienti, ognuno dei quali sembra avere qualcosa da nascondere, qualche segreto forse legato a un fatto di sangue avvenuto molti anni prima. La locanda resta isolata a causa della tormenta e anche il telefono risulta isolato, ma prima che ciò avvenga arriva alla pensione il sergente Trotter della polizia di Scotland Yard, in missione per proteggere ospiti e albergatori da un oscuro assassino psicopatico intenzionato a colpire nuovamente.\r\n\r\nPoco dopo viene ucciso una degli ospiti, la signora Boyle. Trotter indaga sull\'assassinio e mette Mollie e Giles l\'uno contro l\'altro, facendo in entrambi sorgere il sospetto che l\'altro abbia una relazione extra-coniugale...', 'TrappolaPerTopi6a0c17a62bb9a.jpg', 'Mondadori', 1952, 94),
('235672', 'Guida Galattica per Autostoppisti', 'Douglas Adam', 'Fantascienza', '\"Guida galattica per gli autostoppisti\" è un capolavoro della fantascienza umoristica nato dalla mente di Douglas Adams. Se non hai mai avuto il piacere di approcciarti a questa serie, ecco l\'essenziale per non farti trovare impreparato (e per sopravvivere alla demolizione della Terra): \r\nDi cosa parla?\r\nTutto inizia con l\'inglese Arthur Dent, che scopre che la sua casa sta per essere abbattuta per fare spazio a una circonvallazione. Poco dopo, scopre che l\'intero pianeta Terra sta per essere abbattuto per fare spazio a una circonvallazione iperspaziale. Viene salvato dal suo amico Ford Prefect, che si rivela essere un alieno inviato sulla Terra per aggiornare la famosissima Guida galattica per gli autostoppisti. \r\nI pilastri della serie\r\n\r\n    L\'Asciugamano: È l\'oggetto più utile che un autostoppista galattico possa avere. Serve per asciugarsi, per scaldarsi, come arma da mischia o, più semplicemente, per ragioni psicologiche (se hai un asciugamano, la gente penserà che hai con te anche tutto il resto).\r\n    Niente panico (Don\'t Panic): La scritta che compare a grandi lettere rassicuranti sulla copertina della Guida.\r\n    Il numero 42: La \"Risposta alla Domanda Fondamentale sulla Vita, l\'Universo e Tutto Quanto\". Il problema è che nessuno conosce la vera domanda.\r\n    Marvin: L\'androide paranoico con un \"cervello grande come un pianeta\" e una depressione cronica che lo rende uno dei personaggi più amati', 'GuidaGalatticaperAutostoppisti69f2049455844.jpeg', 'Oscar', 1979, 75),
('298765', 'Amleto', 'William Shakespeare', 'Romanzo Storico', 'Amleto (Hamlet (/ˈhæmlɪt/), titolo originale: The Tragedy of Hamlet, Prince of Denmark, ossia La tragedia di Amleto, principe di Danimarca) è una tragedia scritta da William Shakespeare tra il 1599 e il 1601, intorno al trentaseiesimo anno di vita del drammaturgo inglese.[1] È l\'opera teatrale più lunga di Shakespeare. Ambientata in Danimarca, essa narra la storia del principe Amleto e dei suoi tentativi di vendetta contro lo zio Claudio, che ha assassinato il padre di Amleto per impossessarsi del trono e sposare la madre di Amleto. Amleto è considerata fra le \"tragedie più potenti e influenti in lingua inglese\", con una storia che può essere \"rivisitata e adattata apparentemente all\'infinito da altri\". È universalmente considerata una delle più grandi opere teatrali e letterarie di tutti i tempi. Ne sono pervenute tre diverse versioni iniziali: il Primo Quarto (Q1, 1603); il Secondo Quarto (Q2, 1604); e il Primo Folio (F1, 1623). Ogni versione include versi e passaggi mancanti nelle altre.', 'Amleto69f213496d972.jpg', 'La Feltrinelli', 1601, 81),
('3276432345', 'I Malavoglia', 'Giovanni Verga', 'Romanzo Storico', 'I Malavoglia è il romanzo più conosciuto dello scrittore Giovanni Verga, pubblicato a Milano dall\'editore Treves nel 1881. È una delle letture più diffuse e indicate nei programmi di letteratura italiana all\'interno del sistema scolastico italiano. Fa parte del Ciclo dei Vinti.[1]', 'IMalavoglia69f212ecdb2a6.jpeg', 'Treves', 1881, 80),
('32962578', 'Il Cavaliere Inesistente', 'Italo Calvino', 'Avventura', '«Stavolta Calvino si è spinto più a ritroso nei secoli e il suo romanzo si svolge tra i paladini di Carlomagno, in quel Medioevo fuori d\'ogni verosimiglianza storica e geografica che è proprio dei poemi cavallereschi. Ma il sapore delle invenzioni calviniane è più che mai moderno. Quando sarebbe stato possibile dar vita ad Agilulfo, il cavaliere inesistente, se non oggi, nel cuore della più astratta civiltà di massa, in cui la persona umana tanto spesso appare cancellata dietro lo schermo delle funzioni, delle attribuzioni e dei comportamenti prestabiliti? ... Ma quel che più conta è che Il cavaliere inesistente si legge prescindendo da tutti i possibili significati, divertendosi alle avventure di Agilulfo e di Gurdulù, della fiera amazzone Bradamante e del giovane Rambaldo, del cupo Torrismondo, della maliziosa Priscilla e della placida Sofronia. In mezzo al succedersi di trovate buffonesche, di battaglie e duelli e naufragi, non si tarda a scoprire l\'accento solito di Calvino, la sua morale attiva e il suo ironico e malinconico riserbo, la sua aspirazione a una pienezza di vita, a un\'umanità totale.»', 'IlCavaliereInesistente6a0c1bf550a49.jpeg', 'La Feltrinelli', 1959, 104),
('34567327', 'Zanna Bianca', 'Jack London', 'Azione', 'Zanna Bianca è un lupo che cresce in un ambiente selvaggio, fatto di sconfinate distese di neve, cupe foreste e fiumi gelati. Continuo bersaglio della crudeltà e della ferocia dei cani e del Dio-Uomo, capisce in fretta che la vita è una perenne lotta per la sopravvivenza e diventa sempre più aggressivo e diffidente. Ma il mondo degli esseri umani ha in serbo per lui anche molto altro: la scoperta della tenerezza e della fedeltà, grazie a un nuovo padrone che lo conquisterà con la sua pazienza e il suo affetto.', 'ZannaBianca6a0c15f3c50ba.jpg', 'Crescere Edizioni', 1906, 91),
('34567654', 'Le avventure di Tom Sawyer', 'Mark Twain', 'Umoristico', 'Le avventure di Tom Sawyer (The Adventures of Tom Sawyer), noto anche semplicemente come Tom Sawyer, è un romanzo dello scrittore statunitense Mark Twain pubblicato il 9 giugno 1876, che racconta la storia di un ragazzo del Sud, il protagonista eponimo, che cresce lungo il fiume Mississippi. È ambientato tra il 1830 e il 1840 nella città fittizia di St. Petersburg, basata su Hannibal, Missouri, dove Twain visse da ragazzo, sulle rive del grande fiume. Luoghi e persone sono in parte autobiografici, ispirati quindi alla vita dell\'autore, alla sua famiglia ed agli amici d\'infanzia. Nel romanzo, l\'orfano Thomas Sawyer, chiamato col diminutivo Tom, vive diverse avventure, spesso col suo amico Huckleberry Finn. Inizialmente un fallimento commerciale, il libro finì per essere il best-seller delle opere di Twain durante la sua vita.', 'LeavventurediTomSawyer6a0c14908e5b2.jpg', 'La Feltrinelli', 1876, 88),
('345676543', 'Racconti', 'Edgar Allan Poe', 'Horror', 'I Racconti (Tales) o anche Racconti dell\'incubo e del terrore (alcune edizioni riportano i vari titoli separati in edizioni divise: Racconti dell\'incubo, Racconti fantastici, Racconti del mistero e ancora Racconti del terrore), sono una delle prime tre edizioni originali dei racconti scritti da Edgar Allan Poe, pubblicata nel 1845. Si tratta di una raccolta contenente quasi tutti i racconti gotici e di genere horror composti dall\'autore, rivelandosi la completa delle due precedenti.\r\nNel 1840 l\'autore aveva già pubblicato la raccolta Racconti del grottesco e dell\'arabesco, contenente storie riproposte in questa raccolta e novelle diverse che trattano anche di argomenti mistici e fantastici, ma sempre con la solita punta di misterioso e di inquietudine da parte dei personaggi. Al giorno d\'oggi le Case editrici hanno usato quest\'ultima edizione del 1845, aggiungendovi i racconti scritti da Poe tra il 1846 e il 1849 (data della sua prematura morte), intitolando spesso le pubblicazioni come: Tutti i racconti del mistero, dell\'incubo e del terrore, che include anche la piccola sezione dei Racconti fantastici e quelli con protagonista Auguste Dupin, detective della novella I delitti della Rue Morgue.', 'Racconti6a0b1b0662eb4.jpg', 'La Feltrinelli', 1845, 86),
('3756321', 'Orgoglio e Pregiudizio', 'Francesco Pagnozzi', 'Romanzo Storico', 'La famiglia Bennet è composta dai coniugi Bennet e dalle loro cinque figlie: Jane, Elizabeth (detta anche Lizzy), Mary, Catherine (detta anche Kitty) e Lydia. L\'obiettivo della signora Bennet, vista la mancanza di un figlio maschio che possa ereditare la loro tenuta di Longbourn nell\'Hertfordshire, è quello di vedere sposate tutte le figlie con uomini ricchi o per lo meno benestanti. Se la signora Bennet è una donna frivola e sciocca, il signor Bennet è un uomo intelligente, sarcastico e imprevedibile che ama prendersi gioco della moglie senza che quest\'ultima se ne accorga. Egli è molto affezionato a Jane e ad Elizabeth, le sue due figlie preferite, dotate entrambe di un carattere assennato e ragionevole.\r\n\r\nQuando il ricco e celibe signor Bingley si trasferisce a Netherfield, una bella dimora in affitto nelle vicinanze, la signora Bennet freme affinché le figlie gli vengano presentate quanto prima e prega il marito di presentarsi a porgere i propri omaggi al nuovo vicino. La donna intende infatti combinare un matrimonio tra il signor Bingley e una delle sue figlie e non vuole correre il rischio di vederselo accaparrato da qualche altra vicina. Il signor Bennet, nonostante l\'apparente reticenza, fa visita al nuovo arrivato e le figlie gli vengono presentate durante il ballo dato da Sir William Lucas, un vicino di casa dei Bennet. Il signor Bingley è giunto a Netherfield in compagnia delle sue due sorelle, Caroline e la signora Hurst, del marito di quest\'ultima, il signor Hurst, e del suo più caro amico, l’affascinante signor Darcy. È immediatamente evidente la grande ammirazione di Bingley per Jane. Al contrario, Darcy non mostra alcun interesse per la compagnia e quindi viene subito etichettato come uomo orgoglioso ed arrogante. Durante il ballo Elizabeth viene definita da Darcy \"appena passabile”, ciò alimenta l’iniziale antipatia di Elizabeth nei suoi confronti.', 'OrgoglioePregiudizio69f2115ed5e4e.jpg', 'Casa di Fra', 1813, 78),
('4587654', 'Triste Solitario y final', 'Osvaldo Soriano', 'Umoristico', 'Una storia sgangherata e fracassona che solo l\'anarchica penna dell’argentino Osvaldo Soriano poteva partorire. Un anziano Stan Laurel incarica il detective Philip Marlowe di far luce sul perché i produttori non lo facciano più lavorare. L’investigatore si mette al lavoro aiutato da Soriano (l\'autore diventa personaggio) arrivando al cuore dello star system hollywoodiano e finendo per metterlo a soqquadro. Un libro dall\'assunto divertente, ricco di passaggi esilaranti che tuttavia ci lascia un dolcissimo senso di malinconia. La stessa emozione che suscita un clown triste, la stessa impressione di disfatta e tenerezza. Bellissimo!', 'TristeSolitarioyfinal6a0c214a912c9.jpeg', 'Mondadori', 1974, 118),
('51039462', 'Holly', 'Stephen Kink', 'Horror', 'Nel suo ultimo romanzo, ambientato in una tranquilla città del Midwest, il re della narrativa horror Stephen King racconta la vicenda di Bonnie Dahl, che un giorno scompare nel nulla. Quando sua madre Penny chiama l\'agenzia Finders Keepers in cerca di aiuto Holly Gibney è restia ad accettare il caso. Il suo socio, Pete, ha il Covid. Sua madre, con cui ha sempre avuto una relazione complicata, è appena morta. In più Holly dovrebbe essere in ferie. Ma c\'è qualcosa nella voce della signora Dahl che le impedisce di dirle di no.', 'Holly6a0c1e2cd2c4e.jpeg', 'Mondadori', 2023, 110),
('54326701', 'Il Richiamo della Foresta', 'Jack London', 'Azione', 'Il romanzo è ambientato inizialmente in California, nella soleggiata Valle di Santa Clara, nel 1897. Il cane Buck, figlio di un maschio di Sanbernardo e di una femmina pastore scozzese, vive nella grande villa di un magistrato, il giudice Miller. Inizia la \"corsa all\'oro del Klondike\", e aumenta così la richiesta di cani da slitta, unico mezzo di locomozione nella gelata estremità settentrionale del continente americano, e pertanto Buck viene venduto dal giardiniere del suo padrone a un losco e brutale trafficante.\r\n\r\nAffidato a un brutale addestratore di cani («l\'uomo dal maglione rosso»), Buck conosce la «legge della zanna e del bastone», attraverso la quale viene picchiato selvaggiamente, aggiogato a una muta guidata dal cane Spitz e costretto infine a diventare un cane da slitta. Col passare del tempo, Buck impara a difendersi dagli altri cani, e arriva addirittura a uccidere Spitz e diventare capo della muta. Cambiano presto i padroni, ma non diminuiscono i maltrattamenti. Dopo essere stato al servizio di tre cercatori d\'oro litigiosi e incapaci, Buck sta per essere ucciso, ma viene salvato dal cercatore d\'oro John Thornton, che diviene invece un suo caro amico. Buck lo salva più volte da situazioni pericolose e infine gli fa vincere una grossa somma in una scommessa, tirando da solo una slitta con un carico di mille libbre.', 'IlRichiamodellaForesta6a0c157c117fc.jpg', 'La Feltrinelli', 1903, 90),
('5610923', 'Io Sono Vivo, Voi Siete Morti', 'Emmanuel Carrère', 'Biografia', '«Da adolescente» scrive Emmanuel Carrère nel Regno «sono stato un lettore appassionato di Dick e, a differenza della maggior parte delle passioni adolescenziali, questa non si è mai affievolita. Ho riletto a intervalli regolari Ubik, Le tre stimmate di Palmer Eldritch, Un oscuro scrutare, Noi marziani, La svastica sul sole. Consideravo – e considero tuttora – il loro autore una specie di Dostoevskij della nostra epoca». A trentacinque anni, spinto da questa inesausta passione, Carrère decise di raccontare la vita, vissuta e sognata, di Philip K. Dick. Il risultato fu questo libro, in cui, con un\'attenzione chirurgica per il dettaglio e una lucidità mai ottenebrata dalla devozione, Carrère ripercorre le tappe di un\'esistenza che è stata un\'ininterrotta, sfrenata, deragliante indagine sulla realtà, condotta sotto l\'influsso di esperienze trascendentali, abuso di farmaci e di droghe, deliri paranoici, ricoveri in ospedali psichiatrici, crisi mistiche e seduzioni compulsive – e riversata in un corpus di quarantaquattro romanzi e oltre un centinaio di racconti (che hanno a loro volta ispirato, più o meno direttamente, una quarantina di film). Con la sua scrittura al tempo stesso semplice e ipnotica, Carrère costruisce una biografia – intricata e avvincente quanto lo sarà, vent\'anni dopo, quella di Eduard Limonov – che è insieme un romanzo di avventure e un nitido affresco delle pericolose visioni di cui Dick fu artefice e vittima.', 'IoSonoVivo,VoiSieteMorti6a0c18a4eb2a6.jpg', 'Adelphi', 2016, 96),
('56310384', 'Oro', 'Federica Pellegrini', 'Biografia', 'Intrappolata in una costante lotta contro sé stessa, Federica Pellegrini riflette sulla sua passione per le gare, viste come un\'opportunità per sfidare i limiti e sperimentare l\'adrenalina della competizione. La tensione pre-gara e il digiuno diventano rituali di preparazione, alimentando l\'istinto combattivo come fosse un lupo braccato. Inizialmente, le vittorie colmano un vuoto interiore, ma col tempo diventano una ricerca di autorealizzazione. Dedicando le principali sfide vinte a se stessa, la più grande nuotatrice italiana rivendica il coraggio e il sacrificio che ha profuso per ottenere i suoi grandi risultati. Questo enigma interiore potrebbe, agli occhi esterni, farla apparire come una persona difficile da comprendere.', 'Oro6a0c19145c504.jpeg', 'Mondadori', 2021, 97),
('56748392', 'Zona Pericolsa', 'Lee Child', 'Azione', 'Zona pericolosa (Killing floor) è il romanzo d\'esordio di Lee Child, e costituisce il primo capitolo della serie Jack Reacher. L\'edizione originale è del \'97, mentre in Italia è stato pubblicato nel 2000 da Longanesi.\r\n\r\nL\'autore è stato insignito dell\'Anthony Award[1] e del Barry Award[2] per il Miglior romanzo d\'esordio.\r\n\r\nIl libro racconta di Jack Reacher, un militare in congedo che vaga nel Nord America senza una meta precisa. Un pullman di linea lo porta dalla Florida a Margrave, in Georgia, dove viene arrestato per omicidio. Quando scopre che la vittima è suo fratello Joe, dà fondo a tutte le sue risorse per annientare la spietata organizzazione criminale che lo ha fatto uccidere.\r\n\r\nLe avventure di Jack Reacher sono narrate talora in prima persona, talora in terza. Questo libro fa parte della prima categoria.\r\n\r\nDal romanzo è stata tratta una serie TV in otto puntate trasmessa in streaming da Amazon Prime Video a partire dal 4 febbraio 2022. Jack Reacher è interpretato da Alan Michael Ritchson.', 'ZonaPericolsa6a0c1f1910de6.jpg', 'Tea', 1997, 112),
('583245', 'Guerra e Pace', 'Lev Tolstoj', 'Romanzo Storico', 'Guerra e pace, noto anche come La guerra e la pace (in russo Война и мир?, Vojnà i mir, nell\'ortografia originale pre-riforma, Война и миръ; AFI: [vɐjˈna i ˈmʲir]) è un romanzo storico di Lev Tolstoj di fama mondiale.\r\n\r\nScritto tra il 1863 e il 1869 e pubblicato per la prima volta tra il 1865 e il 1869 sulla rivista Russkij Vestnik, riguarda principalmente la storia di due famiglie, i Bolkonskij e i Rostov, tra le guerre napoleoniche, la campagna napoleonica in Russia del 1812 e la fondazione delle prime società segrete russe. Tolstoj paragonava la sua opera alle grandi creazioni omeriche, e nella sua immensità Guerra e pace si potrebbe dire un romanzo infinito, nel senso che l\'autore sembra essere riuscito a trovare la forma perfetta con cui descrivere in letteratura l\'uomo nel tempo. Denso di riferimenti filosofici, scientifici e storici, il racconto sembra unire la forza della storicità e la precisione drammaturgica (persino di Napoleone si fa un ritratto indimenticabile) ad un potente e lucido sguardo metafisico che domina il grande flusso degli eventi, da quelli colossali, come la battaglia di Austerlitz e la battaglia di Borodino, a quelli più intimi.', 'GuerraePace69f20554a0014.jpg', 'La Feltrinelli', 1869, 76),
('643990123', 'Una Terra Promessa', 'Barack Obama', 'Biografia', 'L\'autobiografia del primo presidente afroamericano della storia USA. Pubblicata poco prima della vittoria di Joe Biden (suo vicepresidente) alle elezioni americane, Una terra promessa è il racconto di Obama, dall\'adolescenza alle grandi conquiste. In questo volume Barack Obama racconta in prima persona la sua incredibile odissea dalla ricerca di un\'identità a leader del mondo libero. Dal caucus dell\'Iowa alla memorabile notte del 4 novembre 2008, quando è stato eletto 44° presidente degli Stati Uniti, diventando il primo afroamericano a ricoprire la massima carica della nazione.', 'UnaTerraPromessa6a0c1abc6fc51.jpeg', 'Mondadori', 2020, 101),
('654321098', 'Dalla Terra Alla Luna', 'Jules Vernes', 'Avventura', 'Dalla Terra alla Luna (De la Terre à la Lune, trajet direct en 97 heures 20 minutes) è un romanzo di fantascienza di Jules Verne del 1865, prima parte di un dittico che si chiude con Intorno alla Luna (Autour de la Lune) scritto nel 1870. In questo romanzo Verne anticipa le prime fasi dello storico allunaggio, avvenuto realmente 104 anni dopo con la missione spaziale Apollo 11, il 20 luglio 1969.\r\nTrama\r\n«Dai miei studi risulta la convinzione che noi dovremmo riuscire in una impresa che sembrerebbe impossibile a ogni altra nazione. È questo il piano che, lungamente elaborato, formerà l\'oggetto della mia comunicazione. Esso è degno di voi, degno del Gun Club e non potrà fare a meno di sollevare gran rumore nel mondo.\r\n\r\n- Molto rumore? - chiese un artigliere appassionato.\r\n- Molto rumore nel vero senso della parola - rispose Barbicane. Mannaggia il caspiolo»\r\n', 'DallaTerraAllaLuna6a0c1b7503839.jpeg', 'La Feltrinelli', 1865, 103),
('762781298', 'Fahrenheit 451', 'Ray Bradbury', 'Distopia', 'Fahrenheit 451 è un romanzo distopico del 1953 dello scrittore americano Ray Bradbury. Ambientato in un prossimo futuro, all\'inizio del XXI secolo, presenta la società americana ventura, nella quale i libri sono stati messi fuori legge, pertanto leggere o possedere libri è considerato reato. I \"pompieri\", un apposito corpo istituito per la bisogna, sono impegnati nel bruciare qualsiasi tipo di volume trovino. Il romanzo segue il punto di vista di Guy Montag, un pompiere che si è disilluso del proprio ruolo di censore della letteratura e di distruttore del sapere; alla fine, egli lascia il suo lavoro e si impegna nella preservazione dei libri.\r\n\r\nFahrenheit 451 fu scritto da Bradbury durante il periodo dell\'espansione comunista seguita al secondo conflitto mondiale e l\'era McCarthy, ispirato dai roghi di libri avvenuti nella Germania nazista e dalla repressione ideologica nell\'Unione Sovietica. La motivazione dichiarata da Bradbury per la scrittura del romanzo è cambiata più volte. In un\'intervista radiofonica del 1956, affermò di aver scritto il libro a causa delle sue preoccupazioni circa la minaccia dei roghi di libri negli Stati Uniti. Negli anni successivi, descrisse l\'opera quale un commento su come i mass media riducano l\'interesse per la lettura. In un\'intervista del 1994, Bradbury citò il politicamente corretto come un\'allegoria della censura presente nel libro, definendolo \"il vero nemico di questi tempi\" ed etichettandolo come \"controllo del pensiero e della libertà di parola\".\r\n\r\nNel 1966 il libro fu trasposto in un omonimo film per la regia di François Truffaut e in un omonimo film TV nel 2018 per la regia di Ramin Bahrani. Nel 2004 al libro fu assegnato il premio Retro Hugo come miglior romanzo 1954.\r\n\r\nIn Italia è stato pubblicato per la prima volta nel 1956 con il titolo Gli anni della fenice.', 'Fahrenheit4516a0c221937342.jpg', 'Mondadori', 1953, 119),
('76543450129', 'Le Avventure Di Sherlock Holmes', 'Arthur Conan Doyle', 'Giallo', 'Le avventure di Sherlock Holmes (1892) è una raccolta di 12 racconti di Arthur Conan Doyle, con protagonista Sherlock Holmes. La raccolta venne pubblicata per la prima volta nel 1892, anche se i racconti che la compongono furono editi individualmente sullo Strand Magazine tra il giugno 1891 e il giugno 1892, arricchiti dalle illustrazioni di Sidney Paget.\r\n\r\nNel 1987 il critico e scrittore H. R. F. Keating ha inserito The Adventures of Sherlock Holmes nella lista dei 100 migliori gialli letterari della storia del poliziesco[1].', 'LeAvventureDiSherlockHolmes6a0c171d09b08.jpg', 'Mondadori', 1892, 93),
('765492123', 'ventimilia leghe sotto i mari', 'Jules Vernes', 'Avventura', 'entimila leghe sotto i mari (Vingt mille lieues sous les mers: Tour du monde sous-marin) è un romanzo fantascientifico dello scrittore francese Jules Verne.\r\n\r\nIl romanzo uscì in due parti, la prima nel marzo del 1869 e la seconda nel giugno del 1870, sulla rivista quindicinale di Pierre-Jules Hetzel, il Magasin d\'éducation et de récréation; poi Hetzel pubblicò, nel novembre 1871, un\'edizione deluxe in ottavo, contenente 111 illustrazioni di Alphonse de Neuville e Édouard Riou.\r\n\r\nLa descrizione accurata del sottomarino del capitano Nemo, il Nautilus, precorre il suo tempo, anticipando con straordinaria precisione varie caratteristiche dei sottomarini odierni, se comparate alle primitive navi degli anni sessanta del XIX secolo. L\'ispirazione gli venne dall\'osservazione del sottomarino Plongeur, che figurava all\'Esposizione Universale di Parigi del 1867, che Verne ebbe modo di esaminare.\r\n\r\nL\'opera costituisce il secondo capitolo di una trilogia del mare che inizia con I figli del capitano Grant e si conclude con L\'isola misteriosa.', 'ventimilialeghesottoimari6a0c15107790c.jpeg', 'La Feltrinelli', 1870, 89),
('87654345', 'Il Mastino di Baskerville', 'Arthur Conan Doyle', 'Giallo', 'Londra, 1889. Uno sbadato visitatore ha dimenticato il suo bastone nell\'ufficio del famoso investigatore Sherlock Holmes, il che permette a Holmes e al dottor Watson di formulare ipotesi deduttive sull\'identità dell\'uomo, che sopraggiunge poco dopo: il dottor James Mortimer. Il medico vorrebbe che Holmes indagasse sulla morte di un suo paziente oltre che caro amico, l\'anziano Sir Charles Baskerville, baronetto e proprietario di un maniero nella brughiera di Dartmoor. L\'uomo, notoriamente debole di cuore, è morto nel viale della sua proprietà, Baskerville Hall, apparentemente a causa di un infarto: il suo viso mostrava un\'espressione di terrore e vicino al corpo erano chiaramente visibili le impronte di un grosso mastino. Sir Charles credeva a un\'antica leggenda secondo cui, a partire dal malvagio Hugo Baskerville ai tempi della guerra civile, vari eredi maschi della famiglia sarebbero stati perseguitati e uccisi da un terrificante cane demoniaco...', 'IlMastinodiBaskerville6a0c169ec900d.jpeg', 'Oscar', 1901, 92),
('876543451', 'Dracula', 'Bram Stroker', 'Horror', '“Mi stava vicino, lo vedevo da sopra la spalla, ma nello specchio non si rifletteva!”. In Transilvania per concludere la vendita di una casa londinese al Conte Dracula, discendente di un\'antichissima casata locale, il giovane avvocato Jonathan Harker scopre che il suo cliente è una creatura di mistero e orrore. “Dracula”, archetipo delle infinite storie di vampiri narrate dalla letteratura e dal cinema, mette in scena l\'eterna lotta tra il Bene e il Male, ma anche tra la ragione e l\'istinto, tra le pulsioni più inconfessabili e il perbenismo non solo vittoriano. Una storia scaturita dall\'inconscio ed entrata in tutti i nostri incubi.', 'Dracula6a0c1d5e0ba03.jpeg', 'Mondadori', 1888, 107),
('8765434567', 'Il Sentiero Dei Nidi Di Ragno', 'Italo Calvino', 'Avventura', 'Italia del 1943-1944, periodo della Resistenza.\r\n\r\nIn una cittadina ligure della Riviera di Ponente, Sanremo, tra valli, boschi e luoghi impervi dove la lotta partigiana è più forte, Pin è un bambino ligure di circa dieci anni, orfano di madre e con il padre marinaio irreperibile, abbandonato a sé stesso e in perenne ricerca di amicizie tra gli adulti del vicolo dove vive, e dell\'osteria che frequenta dove viene preso in giro da tutti: Pin è canzonato a causa delle relazioni sessuali che la sorella prostituta intrattiene coi militari tedeschi; provocato dagli adulti a provare la sua fedeltà, Pin sottrae a Frick, un marinaio tedesco amante della donna, la pistola di servizio, una P38, e la sotterra in campagna, nel luogo, sconosciuto a tutti, in cui è solito rifugiarsi, dove i ragni fanno il nido. Il furto sarà poi causa del suo arresto e dell\'internamento in prigione. Qui entra a contatto con la durezza della vita di carcerato e con la violenza perpetrata da uomini su altri uomini. In prigione incontra Pietromagro, il ciabattino di cui era garzone, ma specialmente Lupo Rosso, un giovane e coraggioso partigiano, che in prigione subiva interrogatori e violenze da parte dei fascisti. Lupo Rosso aiuta Pin a evadere dal carcere, ma una volta fuori, per cause indipendenti dalla sua volontà abbandona Pin a sé stesso, a girovagare nel bosco da solo, finché non incontra Cugino, un partigiano solitario alto, grosso e dall\'aria mite. Questi lo condurrà sulle montagne, al gruppo segreto di militanti partigiani a cui appartiene, il distaccamento del Dritto. Qui Pin entra in contatto con una folta casistica umana di antifascisti, dalla dubbia eroicità e caratterizzati dai più comuni difetti umani: Dritto il comandante, Pelle, Carabiniere, Giacinto detto il Commissario, i quattro cognati Duca, Conte, Barone e Marchese, Mancino il cuciniere, Giglia la moglie di Mancino, Zena detto Berretta di Legno o Labbra di Bue e così si sistema presso di loro. ..', 'IlSentieroDeiNidiDiRagno6a0c1c530ec05.jpg', 'Mondadori', 1947, 105),
('987601234', 'Nella Quarta Dimensione', 'Liu Cixin', 'Fantascienza', 'Nella quarta dimensione (死神永生S, Sǐshén yǒngshēngP, lett. \"Morte immortale\") è un romanzo di fantascienza del 2010 dello scrittore cinese Liu Cixin.\r\n\r\nÈ il romanzo conclusivo della trilogia Memoria del passato della Terra (地球往事S, Dìqiú wǎngshìP), ma i lettori cinesi di solito si riferiscono alla serie usando il titolo del primo dei romanzi che la compongono: Il problema dei tre corpi (三體T, 三体S, Sān tǐP, lett. \"Tre corpi\") vincitore del Premio Hugo, il secondo romanzo della trilogia si intitola La materia del cosmo (黑暗森林S, Hēi\'àn sēnlínP, lett. \"La foresta oscura\")', 'NellaQuartaDimensione6a0c2057ab921.jpg', 'Mondadori', 2010, 115);

INSERT INTO `copiaLibro` (`idCopia`, `ISBN`, `Stato`) VALUES
(11, '012378432', 1),
(12, '012378432', 1),
(13, '012378432', 1),
(14, '10234782', 1),
(15, '10234782', 1),
(16, '1025348', 1),
(17, '1025348', 1),
(18, '1029384675', 1),
(19, '1029384675', 1),
(20, '1029384675', 1),
(21, '1029834783', 1),
(22, '1029834783', 1),
(23, '10923864', 1),
(24, '10923864', 1),
(25, '10981234', 1),
(26, '10981234', 1),
(27, '10981234', 1),
(28, '109871234', 1),
(29, '109871234', 1),
(30, '1234', 1),
(31, '1234', 1),
(32, '123456', 1),
(33, '12347647', 1),
(34, '12348765', 1),
(35, '12348765', 1),
(36, '1234987', 1),
(37, '1234987', 1),
(38, '1331979821020', 1),
(39, '1613154538417', 1),
(40, '1876512', 1),
(41, '1876534', 1),
(42, '1876534', 1),
(43, '1876534', 1),
(44, '198723', 1),
(45, '198723', 1),
(46, '198723', 1),
(47, '21089236', 1),
(48, '21089236', 1),
(49, '235672', 1),
(50, '235672', 1),
(51, '298765', 1),
(52, '298765', 1),
(53, '3276432345', 1),
(54, '32962578', 1),
(55, '34567327', 1),
(56, '34567327', 1),
(57, '34567654', 1),
(58, '34567654', 1),
(59, '345676543', 1),
(60, '345676543', 1),
(61, '3756321', 1),
(62, '3756321', 1),
(63, '4587654', 1),
(64, '4587654', 1),
(65, '51039462', 1),
(66, '51039462', 1),
(67, '54326701', 1),
(68, '5610923', 1),
(69, '56310384', 1),
(70, '56748392', 1),
(71, '583245', 1),
(72, '643990123', 1),
(73, '654321098', 1),
(74, '762781298', 1),
(75, '76543450129', 1),
(76, '765492123', 1),
(77, '87654345', 1),
(78, '87654345', 1),
(79, '876543451', 1),
(80, '8765434567', 1),
(81, '987601234', 1);