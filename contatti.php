<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$to = trim((string) ($site["email"] ?? ""));
$documenti = is_array($site["documenti"] ?? null) ? $site["documenti"] : [];
$sent = false;
$error = "";
$values = [
    "nome" => "",
    "email" => "",
    "telefono" => "",
    "interesse" => "messaggio",
    "messaggio" => "",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $values["nome"] = trim((string) ($_POST["nome"] ?? ""));
    $values["email"] = trim((string) ($_POST["email"] ?? ""));
    $values["telefono"] = trim((string) ($_POST["telefono"] ?? ""));
    $values["interesse"] = trim((string) ($_POST["interesse"] ?? "messaggio"));
    $values["messaggio"] = trim((string) ($_POST["messaggio"] ?? ""));
    $honeypot = trim((string) ($_POST["sg_hp_field"] ?? ""));

    if ($honeypot !== "") {
        $sent = false;
        $values = ["nome" => "", "email" => "", "telefono" => "", "interesse" => "messaggio", "messaggio" => ""];
    } elseif ($values["nome"] === "" || $values["email"] === "" || $values["messaggio"] === "") {
        $error = "Compila nome, email e messaggio.";
    } elseif (!filter_var($values["email"], FILTER_VALIDATE_EMAIL)) {
        $error = "Inserisci un’email valida.";
    } elseif ($to === "" || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = "Il form non è ancora collegato a un’email. Scrivici direttamente.";
    } else {
        $body = "Nome: " . $values["nome"] . "\n"
            . "Email: " . $values["email"] . "\n"
            . "Telefono: " . $values["telefono"] . "\n"
            . "Interesse: " . $values["interesse"] . "\n\n"
            . $values["messaggio"] . "\n";
        $ok = data_send_mail($to, "Messaggio dal sito SolGuido · " . $values["nome"], $body, $values["email"]);
        if ($ok) {
            $sent = true;
            $values = ["nome" => "", "email" => "", "telefono" => "", "interesse" => "messaggio", "messaggio" => ""];
        } else {
            $error = "Invio non riuscito. Scrivici a " . $to . ".";
        }
    }
}

$legale = trim((string) ($site["nomeLegale"] ?? "")) ?: ($site["nome"] ?? "SolGuido");
$titolo = trim((string) ($site["contattiTitolo"] ?? "")) ?: "Contatti";
$lead = trim((string) ($site["contattiLead"] ?? ""));
site_header("contatti", $titolo, $lead !== "" ? $lead : $titolo);
?>
<header class="page-head reveal">
  <h1><?= data_h($titolo) ?></h1>
  <?php if ($lead !== "") { ?>
    <p><?= data_h($lead) ?></p>
  <?php } ?>
</header>

<div class="form-board reveal">
  <aside class="contact-aside">
    <h2>Contatti</h2>
    <p><strong><?= data_h($legale) ?></strong></p>
    <?php if (!empty($site["sede"])) { ?>
      <p>Sede legale: <?= data_h($site["sede"]) ?></p>
    <?php } ?>
    <?php if (!empty($site["codiceFiscale"])) { ?>
      <p>CF: <?= data_h($site["codiceFiscale"]) ?></p>
    <?php } ?>
    <?php if (!empty($site["iban"])) { ?>
      <p>IBAN: <?= data_h($site["iban"]) ?></p>
    <?php } ?>
    <?php if (!empty($site["email"])) { ?>
      <a href="mailto:<?= data_h($site["email"]) ?>"><?= data_h($site["email"]) ?></a>
    <?php } ?>
    <?php if (!empty($site["pec"])) { ?>
      <a href="mailto:<?= data_h($site["pec"]) ?>">PEC: <?= data_h($site["pec"]) ?></a>
    <?php } ?>
    <?php if (!empty($site["telefono"])) { ?>
      <a href="tel:<?= data_h(preg_replace('/\s+/', '', (string) $site["telefono"])) ?>">Tel: <?= data_h($site["telefono"]) ?></a>
    <?php } ?>
    <?= site_social_links("inline") ?>

    <?php
    $docsConUrl = array_values(array_filter($documenti, static function ($doc) {
        return trim((string) ($doc["url"] ?? "")) !== "";
    }));
    ?>
    <?php if ($docsConUrl) { ?>
      <h2 style="margin-top:1rem">Documenti</h2>
      <?php foreach ($docsConUrl as $doc) {
          $label = trim((string) ($doc["label"] ?? "Documento"));
          $url = trim((string) ($doc["url"] ?? ""));
          if ($url !== "" && !preg_match('#^(https?:)?//#i', $url)) {
              $url = site_asset(ltrim($url, "/"));
          }
          $downloadName = data_slugify($label) . ".pdf";
          ?>
        <a href="<?= data_h($url) ?>" download="<?= data_h($downloadName) ?>" target="_blank" rel="noopener noreferrer"><?= data_h($label) ?> (PDF)</a>
      <?php } ?>
    <?php } else { ?>
      <p style="margin-top:0.75rem;color:var(--muted)">Atto costitutivo e statuto: in arrivo.</p>
    <?php } ?>
  </aside>

  <form class="site-form" method="post" id="associati" novalidate>
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
      <span>Di cosa si tratta</span>
      <select name="interesse">
        <?php
        $opzioni = [
            "messaggio" => "Messaggio generale",
            "socio" => "Voglio diventare socio",
            "sponsor" => "Proposta di sponsorizzazione",
            "collaborazione" => "Proposta / collaborazione",
            "altro" => "Altro",
        ];
        foreach ($opzioni as $key => $label) {
            $sel = $values["interesse"] === $key ? " selected" : "";
            echo '<option value="' . data_h($key) . '"' . $sel . ">" . data_h($label) . "</option>";
        }
        ?>
      </select>
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
