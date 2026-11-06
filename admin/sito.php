<?php

require __DIR__ . DIRECTORY_SEPARATOR . "lib.php";
admin_boot();
admin_require_login();

$site = data_site();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    admin_csrf_check();
    $site["nome"] = trim((string) ($_POST["nome"] ?? ""));
    $site["tagline"] = trim((string) ($_POST["tagline"] ?? ""));
    $site["chiSiamo"] = trim((string) ($_POST["chiSiamo"] ?? ""));
    $site["cosaFacciamo"] = trim((string) ($_POST["cosaFacciamo"] ?? ""));
    $site["email"] = trim((string) ($_POST["email"] ?? ""));
    $site["instagram"] = trim((string) ($_POST["instagram"] ?? ""));
    $site["facebook"] = trim((string) ($_POST["facebook"] ?? ""));
    $site["codiceFiscale"] = trim((string) ($_POST["codiceFiscale"] ?? ""));
    $site["testo8x1000"] = trim((string) ($_POST["testo8x1000"] ?? ""));
    $site["firmaGuido"] = trim((string) ($_POST["firmaGuido"] ?? ""));

    if ($site["nome"] === "") {
        admin_flash("error", "Il nome dell’associazione è obbligatorio.");
    } elseif (!data_write("sito", $site)) {
        admin_flash("error", "Impossibile scrivere sito.json.");
    } else {
        admin_flash("success", "Testi del sito salvati.");
        header("Location: sito.php");
        exit;
    }
}

ob_start();
?>
<a class="admin-back" href="index.php">← Contenuti</a>
<p class="admin-kicker">Sito</p>
<h1 class="admin-title">Testi e contatti</h1>
<form class="admin-form" method="post">
  <?= admin_csrf_field() ?>
  <label class="admin-field">
    <span>Nome</span>
    <input type="text" name="nome" required value="<?= admin_h($site["nome"]) ?>">
  </label>
  <label class="admin-field">
    <span>Tagline</span>
    <input type="text" name="tagline" value="<?= admin_h($site["tagline"]) ?>">
  </label>
  <label class="admin-field">
    <span>Chi siamo</span>
    <textarea name="chiSiamo"><?= admin_h($site["chiSiamo"]) ?></textarea>
  </label>
  <label class="admin-field">
    <span>Cosa facciamo</span>
    <textarea name="cosaFacciamo"><?= admin_h($site["cosaFacciamo"]) ?></textarea>
  </label>
  <label class="admin-field">
    <span>Email (form contatti)</span>
    <input type="email" name="email" value="<?= admin_h($site["email"]) ?>">
  </label>
  <label class="admin-field">
    <span>Instagram</span>
    <input type="url" name="instagram" value="<?= admin_h($site["instagram"]) ?>">
  </label>
  <label class="admin-field">
    <span>Facebook</span>
    <input type="url" name="facebook" value="<?= admin_h($site["facebook"]) ?>">
  </label>
  <label class="admin-field">
    <span>Codice fiscale (8×1000)</span>
    <input type="text" name="codiceFiscale" value="<?= admin_h($site["codiceFiscale"]) ?>">
  </label>
  <label class="admin-field">
    <span>Testo 8×1000</span>
    <textarea name="testo8x1000"><?= admin_h($site["testo8x1000"]) ?></textarea>
  </label>
  <label class="admin-field">
    <span>Firma Guido (bacheca)</span>
    <input type="text" name="firmaGuido" value="<?= admin_h($site["firmaGuido"]) ?>">
  </label>
  <div class="admin-actions">
    <button class="admin-btn" type="submit">Salva</button>
  </div>
</form>
<?php
admin_layout("Sito", ob_get_clean(), ["nav" => true]);
