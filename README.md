# SolGuido

Sito dell’associazione SolGuido (pattern Ohana): PHP + JSON, hosting Linux Aruba.

## Locale

```bash
php -S localhost:8080 router.php
```

Poi apri `http://localhost:8080/` e `http://localhost:8080/admin/` (prima visita → setup credenziali).

## Deploy Aruba

1. Carica tutta la cartella in `www` (o sottocartella) via FTP/SFTP.
2. Assicurati che `data/` e `media/` siano **scrivibili** dal PHP (chmod 755/775 tipici; se serve 777 solo su quelle cartelle).
3. Verifica estensione **GD** e funzione **mail()** sul piano.
4. Apri `/admin/setup.php` subito e crea username/password.
5. In Admin → Sito imposta email Gmail reale, CF, social.
6. Punta il DNS di `solguido.it` all’hosting e attiva HTTPS da pannello Aruba.

## Contenuti

- Testi: `data/sito.json` (anche da admin)
- Attività: `data/attivita.json` (filtro Prossimi / Passati in pagina)
- Bacheca: `data/bacheca.json` (submit pubblico → `pending` → approvazione admin)
- Contatti: un solo form (niente newsletter)
- Placeholder sfondo home: `img/placeholders/`

`data/` e `includes/` sono bloccati via `.htaccess`. `admin/credentials.php` non è scaricabile.
