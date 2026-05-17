# Alexandria
Sistema web per la gestione di libri, utenti e prestiti in una biblioteca.

NOTA1: /utils/connect.php prende le credenziali da /.env, che ovviamente non è stato caricato nel repo

NOTA2: installare PHP Composer da https://getcomposer.org/download/ ed eseguire "composer install" dal root del repo per installare le dipendenze (in composer.json), e "composer dump-autoload" per generare l'autoloading delle classi.


esempio di .env:
DB_HOST=localhost
DB_USER=user
DB_PASS=pass
DB_NAME=alexandria
EMAIL_USERNAME=example@smtp-server.com
EMAIL_PASSWORD=pass
EMAIL_BIBLIO=biblio@example.com
APP_DEBUG=true
APP_URL=http://localhost/Alexandria