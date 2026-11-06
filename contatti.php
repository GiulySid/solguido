<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$to = trim((string) ($site["email"] ?? ""));
$sent = false;
$error = "";
$values = [
    "nome" => "",
    "email" => "",
    "telefono" => "",
    "messaggio" => "",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $values["nome"] = trim((string) ($_POST["nome"] ?? ""));
    $values["email"] = trim((string) ($_POST["email"] ?? ""));
    $values["telefono"] = trim((string) ($_POST["telefono"] ?? ""));
    $values["messaggio"] = trim((string) ($_POST["messaggio"] ?? ""));
    $honeypot = trim((string) ($_POST["sg_hp_field"] ?? ""));

    if ($honeypot !== "") {
        $sent = false;
        $values = ["nome" => "", "email" => "", "telefono" => "", "messaggio" => ""];
    } elseif ($values["nome"] === "" || $values["email"] === "" || $values["messaggio"] === "") {
        $error = "Compila nome, email e messaggio.";
    } elseif (!filter_var($values["email"], FILTER_VALIDATE_EMAIL)) {
        $error = "Inserisci un’email valida.";
    } elseif ($to === "" || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = "Il form non è ancora collegato a un’email. Scrivici direttamente.";
    } else {
        $body = "Nome: " . $values["nome"] . "\n"
            . "Email: " . $values["email"] . "\n"
            . "Telefono: " . $values["telefono"] . "\n\n"
            . $values["messaggio"] . "\n";
        $ok = data_send_mail($to, "Messaggio dal sito SolGuido · " . $values["nome"], $body, $values["email"]);
        if ($ok) {
            $sent = true;
            $values = ["nome" => "", "email" => "", "telefono" => "", "messaggio" => ""];
        } else {
            $error = "Invio non riuscito. Scrivici a " . $to . ".";
        }
    }
}

site_header("contatti", "Contatti", "Scrivi a SolGuido.");
?>
<header class="page-head reveal">
  <h1>Scrivici</h1>
  <p>Per collaborazioni, eventi o se vuoi associarti.</p>
</header>

<div class="form-board reveal">
  <aside class="contact-aside">
    <h2>Dove trovarci</h2>
    <?php if (!empty($site["email"])) { ?>
      <a href="mailto:<?= data_h($site["email"]) ?>"><?= data_h($site["email"]) ?></a>
    <?php } ?>
    <?php if (!empty($site["instagram"])) { ?>
      <a href="<?= data_h($site["instagram"]) ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
    <?php } ?>
    <?php if (!empty($site["facebook"])) { ?>
      <a href="<?= data_h($site["facebook"]) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
    <?php } ?>
  </aside>

  <form class="site-form" method="post" novalidate>
    <?php if ($sent) { ?>
      <p class="form-note form-note--ok">Grazie. Ti rispondiamo appena possibile.</p>
    <?php } ?>
    <?php if ($error !== "") { ?>
      <p class="form-note form-note--err"><?= data_h($error) ?></p>
    <?php } ?>
    <label class="hp" for="sg_hp_field">Azienda
      <input type="text" id="sg_hp_field" name="sg_hp_field" tabindex="-1" autocomplete="off">
    </label>
    <label>
      <span>Nome</span>
      <input type="text" name="nome" required maxlength="120" value="<?= data_h($values["nome"]) ?>">
    </label>
    <label>
      <span>Email</span>
      <input type="email" name="email" required maxlength="160" value="<?= data_h($values["email"]) ?>">
    </label>
    <label>
      <span>Telefono</span>
      <input type="text" name="telefono" maxlength="40" value="<?= data_h($values["telefono"]) ?>">
    </label>
    <label>
      <span>Messaggio</span>
      <textarea name="messaggio" required maxlength="4000"><?= data_h($values["messaggio"]) ?></textarea>
    </label>
    <button class="btn" type="submit">Invia</button>
  </form>
</div>
<?php
site_footer();
