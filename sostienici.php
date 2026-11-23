<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$cf = trim((string) ($site["codiceFiscale"] ?? ""));
$iban = trim((string) ($site["iban"] ?? ""));
$quote = is_array($site["quoteSocio"] ?? null) ? $site["quoteSocio"] : [];
$modi = is_array($site["modiSostegno"] ?? null) ? $site["modiSostegno"] : [];
$titolo = trim((string) ($site["sostieniciTitolo"] ?? "")) ?: "Sostienici";
$intro = trim((string) ($site["testoSostienici"] ?? ""));
site_header("sostienici", $titolo, $intro !== "" ? $intro : $titolo);
?>
<header class="page-head reveal">
  <h1><?= data_h($titolo) ?></h1>
  <?php if ($intro !== "") { ?>
    <p><?= data_h($intro) ?></p>
  <?php } ?>
</header>

<section class="section reveal">
  <div class="section__head">
    <div>
      <h2>Diventa socio</h2>
    </div>
  </div>
  <?php if (!empty($site["sostieniciSocioIntro"])) { ?>
    <p class="prose"><?= data_h($site["sostieniciSocioIntro"]) ?></p>
  <?php } ?>
  <?php if ($quote) { ?>
    <ul class="support-tiers">
      <?php foreach ($quote as $q) {
          $nome = trim((string) ($q["nome"] ?? ""));
          $dettaglio = trim((string) ($q["dettaglio"] ?? ""));
          if ($nome === "") {
              continue;
          }
          ?>
        <li>
          <strong><?= data_h($nome) ?></strong>
          <?php if ($dettaglio !== "") { ?>
            <span><?= data_h($dettaglio) ?></span>
          <?php } ?>
        </li>
      <?php } ?>
    </ul>
  <?php } ?>
  <p style="margin-top:1.25rem">
    <a class="btn" href="<?= data_h(site_asset("contatti")) ?>#associati">Compila il modulo</a>
  </p>
</section>

<?php if ($modi) { ?>
<section class="section reveal">
  <div class="section__head">
    <div>
      <h2>Altri modi per sostenerci</h2>
    </div>
  </div>
  <ul class="link-list">
    <?php foreach ($modi as $modo) {
        $modo = trim((string) $modo);
        if ($modo === "" || stripos($modo, "diventa socio") === 0) {
            continue;
        }
        if (stripos($modo, "5") !== false) {
            echo '<li><a href="#cinque-per-mille">' . data_h($modo) . "</a></li>";
        } elseif (stripos($modo, "donazione") !== false) {
            echo '<li><a href="#donazione">' . data_h($modo) . "</a></li>";
        } else {
            echo "<li><a href=\"" . data_h(site_asset("contatti")) . "\">" . data_h($modo) . "</a></li>";
        }
    } ?>
  </ul>
</section>
<?php } ?>

<section class="form-board reveal" id="donazione">
  <aside class="support-card">
    <h2>Donazione volontaria</h2>
    <?php if ($iban !== "") { ?>
      <p class="cf-box cf-box--iban"><?= data_h($iban) ?></p>
      <?php if (!empty($site["testoDonazione"])) { ?>
        <div class="prose"><?= data_rich($site["testoDonazione"]) ?></div>
      <?php } ?>
    <?php } ?>
    <a class="btn-ghost" href="<?= data_h(site_asset("contatti")) ?>"><?= data_h($site["contattiTitolo"] ?: "Scrivici") ?></a>
  </aside>
  <div class="prose" id="cinque-per-mille">
    <h2>5×1000</h2>
    <?= data_rich($site["testo5x1000"] ?? "") ?>
    <?php if ($cf !== "") { ?>
      <div class="cf-box" role="group" aria-label="Codice fiscale">
        <span class="cf-box__label">Codice fiscale</span>
        <span class="cf-box__value"><?= data_h($cf) ?></span>
      </div>
    <?php } ?>
  </div>
</section>
<?php
site_footer();
