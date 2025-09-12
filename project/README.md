# Návod pro spuštění

## 1. Rozbalení archivu

Nejprve rozbalte archiv do požadovaného adresáře.

Přejděte do adresáře s implementací:

```bash
cd prilohy\impl
```

## 2. Instalace závislostí

Nainstalujte PHP balíčky pomocí Composeru:

```bash
composer install
```

## 3. Nastavení prostředí

Symfony používá soubor .env pro konfiguraci prostředí, včetně připojení k databázi a e-mailovému serveru..

Otevřete soubor .env a nastavte následující proměnné:

```dotenv
DATABASE_URL="postgresql://[username]:[password]@127.0.0.1:5432/[db_name]?serverVersion=16&charset=utf8"
MAILER_DSN="smtp://[your_username]:[your_password]@[smtp.server.com]:[port]"
```

Nahraďte:

- username, password, db_name – přihlašovací údaje k databázi PostgreSQL.
- your_username, your_password – přihlašovací údaje k SMTP serveru (např. z Mailtrap).
- smtp.server.com – adresa SMTP serveru.
- port – port SMTP serveru (např. 2525 pro Mailtrap).

## 4. Vytvoření databáze (pokud ještě neexistuje)

```bash
php bin/console doctrine:database:create
```

Tento příkaz vytvoří databázi podle nastavení ve vašem souboru .env.

## 5. Spuštění migrací

```bash
php bin/console doctrine:migrations:migrate
```

## 6. Načtení dat z dumpu databáze

Pro načtení pouze dat z předem připraveného SQL souboru spusťte:

```bash
psql -f ..\test_data\dump_db.sql "postgresql://my_user:my_password@127.0.0.1:5432/my_database"
```

Ujistěte se, že cesta k souboru a údaje k databázi odpovídají vaší konfiguraci.

## 7. Spuštění vývojového serveru

```bash
php -S localhost:8080 -t public/
```

## 8. Spuštění fronty (Messenger Consumer)

Symfony používá Messenger pro asynchronní zpracování úloh. Spusťte posluchače:

```bash
php bin/console messenger:consume async scheduler_expired_goals_schedule -vv
```

## 9. Přednastavení uživatelé

Po načtení dat z dumpu budou v systému k dispozici následující uživatelé:

| Uživatelské jméno | Heslo     | Role       |
| ----------------- | --------- | ---------- |
| `admin`           | `admin`   | ROLE_ADMIN |
| `petra_n91`       | `petra91` | ROLE_USER  |
| `tomik_d87`       | `tomik87` | ROLE_USER  |
| `funt_14`         | `funt14`  | ROLE_USER  |
