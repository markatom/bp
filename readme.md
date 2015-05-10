# Požadavky

Pro běh systému je nutné mít nainstalováno:

 * PHP 5.5 a vyšší
 * MySQL 5.0 a vyšší
 * Apache 2.0 a vyšší

Dále je nutné splnit požadavky Nette Frameworku: http://doc.nette.org/cs/2.3/requirements

# Spuštění aplikace

Aplikace ke svému běhu vyžaduje vytvořenou MySQL databázi s kódováním `utf8_czech_ci` a e-mailovou schránku, ke které
je možné přistupovat pomocí SMTP a IMAP protokolu. Také je důležité umožnit Apache zapisovat do adresářů `log` a `temp`.

V adresáři `app/config` se nachází soubor `local.neon`, do kterého je potřeba doplnit údaje pro připojení k databázi a
e-mailové schránce.

Poté je nutné pomocí Doctrine vytvořit databázové schéma. O to se postará příkaz:

    $ bin/console orm:schema-tool:create
    
Pro nahrání testovacích dat do databáze slouží skript:

    $ bin/load_fixtures
    
Systém poté spustíme pomocí webového serveru Apache. Aplikace je připravena pro běh s Document Root nastaveným přímo
do adresáře implementace nebo podadresáře `www`.

# Spuštění testů

Pro spuštění testů je vyžadována Java. V konfiguračním souboru `tests/behavioural/config.yaml` je nutné upravit
řádek 10, kam je potřeba doplnit URL adresu, na které běží aplikace, například:

    base_url: http://localhost/bp

K samotnému spuštění testů poté slouží skript:

    $ bin/run_tests
    
Běh testů je odladěn pro prohlížeč Firefox ve verzi 37.
