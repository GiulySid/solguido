<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "dispatch.php";
site_dispatch();

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$name = $site["nome"] ?? "SolGuido";
$upcoming = data_attivita_upcoming(3);

$heroImages = [
    "img/placeholders/home-bg-1.svg",
    "img/placeholders/home-bg-2.jpg",
    "img/placeholders/home-bg-3.svg",
];

site_header("home", $name, $site["tagline"] ?? "");
?>
<section class="hero">
  <div class="hero__media" aria-hidden="true">
    <?php foreach ($heroImages as $src) {
        if (!is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $src))) {
            continue;
        }
        ?>
      <img src="<?= data_h(site_asset($src)) ?>" alt="">
    <?php } ?>
  </div>
  <div class="hero__veil" aria-hidden="true"></div>
  <p class="hero__brand"><?= data_h($name) ?></p>
  <?php if (!empty($site["tagline"])) { ?>
    <h1><?= data_h($site["tagline"]) ?></h1>
  <?php } ?>
  <?php if (!empty($site["heroLead"])) { ?>
    <div class="hero__lead"><?= data_rich($site["heroLead"]) ?></div>
  <?php } ?>
  <div class="hero__cta">
    <a class="btn" href="<?= data_h(site_asset("contatti")) ?>"><?= data_h($site["contattiTitolo"] ?: "Scrivici") ?></a>
    <?= site_social_links("hero") ?>
  </div>
</section>

<?php
$chiSiamoTitolo = trim((string) ($site["chiSiamoTitolo"] ?? "")) ?: "Chi siamo";
$chiSiamoPreview = trim((string) ($site["chiSiamoAnteprima"] ?? ""));
if ($chiSiamoPreview !== "") {
?>
  <section class="section panel panel--golden reveal">
    <div class="section__head">
      <div>
        <h2><?= data_h($chiSiamoTitolo) ?></h2>
      </div>
    </div>
    <div class="prose">
      <?= data_rich($chiSiamoPreview) ?>
      <p><a class="btn" href="<?= data_h(site_asset("chi-siamo")) ?>">Continua a leggere</a></p>
    </div>
  </section>
<?php } ?>

<?php if ($upcoming) { ?>
  <section class="section reveal">
    <div class="section__head">
      <div>
        <h2><?= data_h($site["attivitaTitolo"] ?: "Prossimi eventi") ?></h2>
      </div>
      <a class="btn-ghost" href="<?= data_h(site_asset("attivita")) ?>?tab=future">Tutti gli eventi</a>
    </div>
    <div class="event-list">
      <?php foreach ($upcoming as $item) {
          $dt = date_create((string) ($item["data"] ?? ""));
          $day = $dt ? $dt->format("j") : "-";
          $month = $dt ? $dt->format("M") : "";
          ?>
        <a class="event-card reveal" href="<?= data_h(site_asset("attivita-dettaglio")) ?>?id=<?= data_h($item["id"]) ?>">
          <div class="event-card__date">
            <strong><?= data_h($day) ?></strong>
            <span><?= data_h($month) ?></span>
          </div>
          <div>
            <h3><?= data_h($item["titolo"] ?? "") ?></h3>
            <p><?= data_h(site_attivita_meta($item)) ?></p>
          </div>
        </a>
      <?php } ?>
    </div>
  </section>
<?php } ?>

<?php if (!empty($site["homeCtaTitolo"]) || !empty($site["homeCtaTesto"])) { ?>
  <section class="section home-cta reveal">
    <?php if (!empty($site["homeCtaTitolo"])) { ?>
      <h2><?= data_h($site["homeCtaTitolo"]) ?></h2>
    <?php } ?>
    <?php if (!empty($site["homeCtaTesto"])) { ?>
      <div class="home-cta__text"><?= data_rich($site["homeCtaTesto"]) ?></div>
    <?php } ?>
    <div class="hero__cta">
      <a class="btn" href="<?= data_h(site_asset("sostienici")) ?>"><?= data_h($site["sostieniciTitolo"] ?: "Sostienici") ?></a>
      <a class="btn-ghost" href="<?= data_h(site_asset("contatti")) ?>#associati">Diventa socio</a>
    </div>
  </section>
<?php } ?>

<?php
$rassegna = is_array($site["rassegnaStampa"] ?? null) ? $site["rassegnaStampa"] : [];
$rassegnaTitolo = trim((string) ($site["rassegnaTitolo"] ?? "")) ?: "Rassegna stampa";
$rassegnaVuoto = trim((string) ($site["rassegnaVuoto"] ?? ""));
if ($rassegna || $rassegnaVuoto !== "") {
?>
  <section class="section reveal">
    <div class="section__head">
      <div>
        <h2><?= data_h($rassegnaTitolo) ?></h2>
      </div>
    </div>
    <?php if (!$rassegna) { ?>
      <p class="prose"><?= data_h($rassegnaVuoto) ?></p>
    <?php } else { ?>
      <ul class="link-list">
        <?php foreach ($rassegna as $item) {
            $label = trim((string) ($item["label"] ?? $item["titolo"] ?? ""));
            $url = trim((string) ($item["url"] ?? ""));
            if ($label === "") {
                continue;
            }
            ?>
          <li>
            <?php if ($url !== "") { ?>
              <a href="<?= data_h($url) ?>" target="_blank" rel="noopener noreferrer"><?= data_h($label) ?></a>
            <?php } else { ?>
              <?= data_h($label) ?>
            <?php } ?>
          </li>
        <?php } ?>
      </ul>
    <?php } ?>
  </section>
<?php } ?>
<?php
site_footer();
