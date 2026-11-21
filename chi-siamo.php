<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$rassegna = is_array($site["rassegnaStampa"] ?? null) ? $site["rassegnaStampa"] : [];
$titolo = trim((string) ($site["chiSiamoTitolo"] ?? "")) ?: "Chi siamo";
$lead = trim((string) ($site["chiSiamoLead"] ?? ""));
site_header("chi-siamo", $titolo, $lead !== "" ? $lead : $titolo);
?>
<header class="page-head reveal">
  <h1><?= data_h($titolo) ?></h1>
  <?php if ($lead !== "") { ?>
    <p><?= data_h($lead) ?></p>
  <?php } ?>
</header>
<div class="prose reveal">
  <?= data_nl2p($site["chiSiamo"] ?? "") ?>
</div>

<section class="section reveal">
  <div class="section__head">
    <div>
      <h2><?= data_h($site["rassegnaTitolo"] ?: "Rassegna stampa") ?></h2>
    </div>
  </div>
  <?php if (!$rassegna) { ?>
    <?php if (!empty($site["rassegnaVuoto"])) { ?>
      <p class="prose"><?= data_h($site["rassegnaVuoto"]) ?></p>
    <?php } ?>
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
<?php
site_footer();
