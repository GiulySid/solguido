<?php

require __DIR__ . DIRECTORY_SEPARATOR . "lib.php";
admin_boot();
admin_require_login();

$site = data_site();

function admin_parse_lines_label_detail($raw) {
    $out = [];
    foreach (preg_split("/\r\n|\n|\r/", (string) $raw) as $line) {
        $line = trim($line);
        if ($line === "") {
            continue;
        }
        if (strpos($line, "|") !== false) {
            [$nome, $dettaglio] = array_map("trim", explode("|", $line, 2));
            if ($nome !== "") {
                $out[] = ["nome" => $nome, "dettaglio" => $dettaglio];
            }
        } else {
            $out[] = ["nome" => $line, "dettaglio" => ""];
        }
    }
    return $out;
}

function admin_parse_simple_lines($raw) {
    $out = [];
    foreach (preg_split("/\r\n|\n|\r/", (string) $raw) as $line) {
        $line = trim($line);
        if ($line !== "") {
            $out[] = $line;
        }
    }
    return $out;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    admin_csrf_check();
    $fields = [
        "nome", "nomeLegale", "tagline", "heroLead",
        "associazioneTitolo", "associazione",
        "ispirazioneTitolo", "ispirazione",
        "conosciGuidoTitolo", "conosciGuido",
        "chiSiamoTitolo", "chiSiamoLead", "chiSiamoAnteprima",
        "attivitaTitolo", "attivitaLead",
        "bachecaTitolo", "bachecaLead",
        "contattiTitolo", "contattiLead",
        "sostieniciTitolo", "testoSostienici", "sostieniciSocioIntro", "testoDonazione",
        "homeCtaTitolo", "homeCtaTesto",
        "rassegnaTitolo", "rassegnaVuoto",
        "email", "pec", "telefono", "sede", "iban",
        "instagram", "facebook", "codiceFiscale", "testo5x1000", "firmaGuido",
    ];
    foreach ($fields as $field) {
        $site[$field] = trim((string) ($_POST[$field] ?? ""));
    }

    $site["quoteSocio"] = admin_parse_lines_label_detail($_POST["quoteSocio"] ?? "");
    $site["modiSostegno"] = admin_parse_simple_lines($_POST["modiSostegno"] ?? "");

    $atto = trim((string) ($_POST["docAtto"] ?? ""));
    $statuto = trim((string) ($_POST["docStatuto"] ?? ""));
    $site["documenti"] = [
        ["label" => "Atto costitutivo", "url" => $atto],
        ["label" => "Statuto", "url" => $statuto],
    ];

    $rassegna = [];
    foreach (admin_parse_simple_lines($_POST["rassegnaStampa"] ?? "") as $line) {
        if (strpos($line, "|") !== false) {
            [$label, $url] = array_map("trim", explode("|", $line, 2));
            if ($label !== "") {
                $rassegna[] = ["label" => $label, "url" => $url];
            }
        } else {
            $rassegna[] = ["label" => $line, "url" => ""];
        }
    }
    $site["rassegnaStampa"] = $rassegna;

    if ($site["nome"] === "") {
        admin_flash("error", "Il nome dell’organizzazione è obbligatorio.");
    } elseif (!data_write("sito", $site)) {
        admin_flash("error", "Impossibile scrivere sito.json.");
    } else {
        admin_flash("success", "Testi del sito salvati.");
        header("Location: sito.php");
        exit;
    }
}

$docs = is_array($site["documenti"] ?? null) ? $site["documenti"] : [];
$docAtto = "";
$docStatuto = "";
foreach ($docs as $doc) {
    $label = strtolower((string) ($doc["label"] ?? ""));
    if (strpos($label, "atto") !== false) {
        $docAtto = (string) ($doc["url"] ?? "");
    }
    if (strpos($label, "statuto") !== false) {
        $docStatuto = (string) ($doc["url"] ?? "");
    }
}

$quoteLines = [];
foreach (is_array($site["quoteSocio"] ?? null) ? $site["quoteSocio"] : [] as $q) {
    $nome = trim((string) ($q["nome"] ?? ""));
    $det = trim((string) ($q["dettaglio"] ?? ""));
    if ($nome === "") {
        continue;
    }
    $quoteLines[] = $det !== "" ? ($nome . " | " . $det) : $nome;
}

$rassegnaLines = [];
foreach (is_array($site["rassegnaStampa"] ?? null) ? $site["rassegnaStampa"] : [] as $item) {
    $label = trim((string) ($item["label"] ?? ""));
    $url = trim((string) ($item["url"] ?? ""));
    if ($label === "") {
        continue;
    }
    $rassegnaLines[] = $url !== "" ? ($label . " | " . $url) : $label;
}

$modiLines = is_array($site["modiSostegno"] ?? null) ? $site["modiSostegno"] : [];

ob_start();
?>
<a class="admin-back" href="index.php">← Contenuti</a>
<h1 class="admin-title">Testi e contatti</h1>
<form class="admin-form" method="post">
  <?= admin_csrf_field() ?>

  <p class="admin-lead">Identità</p>
  <label class="admin-field"><span>Nome brand</span><input type="text" name="nome" required value="<?= admin_h($site["nome"]) ?>"></label>
  <label class="admin-field"><span>Nome legale</span><input type="text" name="nomeLegale" value="<?= admin_h($site["nomeLegale"] ?? "") ?>"></label>
  <label class="admin-field"><span>Tagline</span><input type="text" name="tagline" value="<?= admin_h($site["tagline"]) ?>"></label>
  <label class="admin-field"><span>Hero (testo sotto la tagline)</span><textarea name="heroLead"><?= admin_h($site["heroLead"] ?? "") ?></textarea></label>
  <label class="admin-field"><span>Home CTA titolo</span><input type="text" name="homeCtaTitolo" value="<?= admin_h($site["homeCtaTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Home CTA testo</span><textarea name="homeCtaTesto"><?= admin_h($site["homeCtaTesto"] ?? "") ?></textarea></label>

  <p class="admin-lead">Chi siamo</p>
  <label class="admin-field"><span>Titolo pagina</span><input type="text" name="chiSiamoTitolo" value="<?= admin_h($site["chiSiamoTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Sottotitolo pagina</span><input type="text" name="chiSiamoLead" value="<?= admin_h($site["chiSiamoLead"] ?? "") ?>"></label>
  <label class="admin-field"><span>Anteprima home</span><textarea name="chiSiamoAnteprima"><?= admin_h($site["chiSiamoAnteprima"] ?? "") ?></textarea></label>
  <label class="admin-field"><span>L’associazione — titolo</span><input type="text" name="associazioneTitolo" value="<?= admin_h($site["associazioneTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>L’associazione — testo</span><textarea name="associazione"><?= admin_h($site["associazione"] ?? "") ?></textarea></label>
  <label class="admin-field"><span>Ispirazione — titolo</span><input type="text" name="ispirazioneTitolo" value="<?= admin_h($site["ispirazioneTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Ispirazione — testo</span><textarea name="ispirazione"><?= admin_h($site["ispirazione"] ?? "") ?></textarea></label>
  <label class="admin-field"><span>Conosci Guido? — titolo</span><input type="text" name="conosciGuidoTitolo" value="<?= admin_h($site["conosciGuidoTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Conosci Guido? — testo</span><textarea name="conosciGuido"><?= admin_h($site["conosciGuido"] ?? "") ?></textarea></label>

  <p class="admin-lead">Rassegna stampa (home)</p>
  <label class="admin-field"><span>Rassegna titolo</span><input type="text" name="rassegnaTitolo" value="<?= admin_h($site["rassegnaTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Rassegna testo vuoto</span><input type="text" name="rassegnaVuoto" value="<?= admin_h($site["rassegnaVuoto"] ?? "") ?>"></label>
  <label class="admin-field"><span>Rassegna stampa (Titolo | URL)</span><textarea name="rassegnaStampa"><?= admin_h(implode("\n", $rassegnaLines)) ?></textarea></label>

  <p class="admin-lead">Eventi / Ricordi</p>
  <label class="admin-field"><span>Eventi titolo</span><input type="text" name="attivitaTitolo" value="<?= admin_h($site["attivitaTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Eventi sottotitolo</span><input type="text" name="attivitaLead" value="<?= admin_h($site["attivitaLead"] ?? "") ?>"></label>
  <label class="admin-field"><span>Ricordi titolo</span><input type="text" name="bachecaTitolo" value="<?= admin_h($site["bachecaTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Ricordi sottotitolo</span><input type="text" name="bachecaLead" value="<?= admin_h($site["bachecaLead"] ?? "") ?>"></label>

  <p class="admin-lead">Sostienici</p>
  <label class="admin-field"><span>Titolo</span><input type="text" name="sostieniciTitolo" value="<?= admin_h($site["sostieniciTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Intro</span><input type="text" name="testoSostienici" value="<?= admin_h($site["testoSostienici"] ?? "") ?>"></label>
  <label class="admin-field"><span>Intro quote socio</span><input type="text" name="sostieniciSocioIntro" value="<?= admin_h($site["sostieniciSocioIntro"] ?? "") ?>"></label>
  <label class="admin-field"><span>Quote socio (Nome | dettaglio)</span><textarea name="quoteSocio"><?= admin_h(implode("\n", $quoteLines)) ?></textarea></label>
  <label class="admin-field"><span>Altri modi (una riga ciascuno)</span><textarea name="modiSostegno"><?= admin_h(implode("\n", $modiLines)) ?></textarea></label>
  <label class="admin-field"><span>Testo donazione (sotto IBAN)</span><textarea name="testoDonazione"><?= admin_h($site["testoDonazione"] ?? "") ?></textarea></label>
  <label class="admin-field"><span>Testo 5×1000</span><textarea name="testo5x1000"><?= admin_h($site["testo5x1000"] ?? "") ?></textarea></label>

  <p class="admin-lead">Contatti e documenti</p>
  <label class="admin-field"><span>Contatti titolo</span><input type="text" name="contattiTitolo" value="<?= admin_h($site["contattiTitolo"] ?? "") ?>"></label>
  <label class="admin-field"><span>Contatti sottotitolo</span><input type="text" name="contattiLead" value="<?= admin_h($site["contattiLead"] ?? "") ?>"></label>
  <label class="admin-field"><span>Email</span><input type="email" name="email" value="<?= admin_h($site["email"]) ?>"></label>
  <label class="admin-field"><span>PEC</span><input type="email" name="pec" value="<?= admin_h($site["pec"] ?? "") ?>"></label>
  <label class="admin-field"><span>Telefono</span><input type="text" name="telefono" value="<?= admin_h($site["telefono"] ?? "") ?>"></label>
  <label class="admin-field"><span>Sede legale</span><input type="text" name="sede" value="<?= admin_h($site["sede"] ?? "") ?>"></label>
  <label class="admin-field"><span>IBAN</span><input type="text" name="iban" value="<?= admin_h($site["iban"] ?? "") ?>"></label>
  <label class="admin-field"><span>Codice fiscale</span><input type="text" name="codiceFiscale" value="<?= admin_h($site["codiceFiscale"]) ?>"></label>
  <label class="admin-field"><span>Instagram</span><input type="url" name="instagram" value="<?= admin_h($site["instagram"]) ?>"></label>
  <label class="admin-field"><span>Facebook</span><input type="url" name="facebook" value="<?= admin_h($site["facebook"]) ?>"></label>
  <label class="admin-field"><span>Link atto costitutivo</span><input type="text" name="docAtto" value="<?= admin_h($docAtto) ?>"></label>
  <label class="admin-field"><span>Link statuto</span><input type="text" name="docStatuto" value="<?= admin_h($docStatuto) ?>"></label>
  <label class="admin-field"><span>Emoji in descrizione Ricordi</span><input type="text" name="firmaGuido" value="<?= admin_h($site["firmaGuido"]) ?>"></label>

  <div class="admin-actions">
    <button class="admin-btn" type="submit">Salva</button>
  </div>
</form>
<?php
admin_layout("Sito", ob_get_clean(), ["nav" => true]);
