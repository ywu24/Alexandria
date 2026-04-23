<?php
class DatabaseConnection
{
    // Istanza singleton per la connessione al database
    private static $instance;
    // Oggetto PDO per la connessione al database
    private $pdo;

    // Costruttore PRIVATO per la classe
    private function __construct()
    {
        // Verifica se esiste il file di configurazione .env
        if (file_exists(__DIR__ . '/../.env')) {
            // Carica le variabili d'ambiente dal file .env
            $env = parse_ini_file(__DIR__ . '/../.env');
            foreach ($env as $key => $value) {
                putenv("$key=$value");
            }
        } else {
            throw new RuntimeException('Error: .env file not found');
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER');
        $password = getenv('DB_PASS');
        $database = getenv('DB_NAME');

        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        // Opzioni per la connessione al database
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('DB connection failed: ' . $e->getMessage());
        }
    }

    // Metodo per ottenere l'istanza singleton
    public static function getInstance()
    {
        // Verifica se l'istanza è già stata creata
        if (self::$instance === null) {
            // Creazione dell'istanza
            self::$instance = new self();
        }
        // Restituzione dell'istanza
        return self::$instance;
    }

    // Metodo per ottenere la connessione al database
    public function getConnection()
    {
        return $this->pdo;
    }

    private function __clone()
    { } // Impedisce la clonazione dell'istanza
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

/*
è considerato best practice NON chiudere il tag PHP alla fine del
file per evitare problemi di spazi bianchi o output indesiderato (se il file finisce con script PHP)
 */