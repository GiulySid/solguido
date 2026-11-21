<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$titolo = trim((string) ($site["chiSiamoTitolo"] ?? "")) ?: "Chi siamo";
$lead = trim((string) ($site["chiSiamoLead"] ?? ""));
site_header("chi-siamo", $titolo, $lead !== "" ? $lead : $titolo);

$associazioneTitolo = trim((string) ($site["associazioneTitolo"] ?? ""));
$associazione = trim((string) ($site["associazione"] ?? ""));
$ispirazioneTitolo = trim((string) ($site["ispirazioneTitolo"] ?? ""));
$ispirazione = trim((string) ($site["ispirazione"] ?? ""));
$conosciGuidoTitolo = trim((string) ($site["conosciGuidoTitolo"] ?? ""));
$conosciGuido = trim((string) ($site["conosciGuido"] ?? ""));
?>
<header class="page-head reveal">
  <h1><?= data_h($titolo) ?></h1>
  <?php if ($lead !== "") { ?>
    <p><?= data_h($lead) ?></p>
  <?php } ?>
</header>

<?php if ($associazioneTitolo !== "" || $associazione !== "") { ?>
  <section class="section panel panel--orchid reveal">
    <div class="section__head">
      <div>
        <h2><?= data_h($associazioneTitolo !== "" ? $associazioneTitolo : "L’associazione") ?></h2>
      </div>
    </div>
    <?php if ($associazione !== "") { ?>
      <div class="prose">
        <?= data_nl2p($associazione) ?>
      </div>
    <?php } ?>
  </section>
<?php } ?>

<?php if ($ispirazioneTitolo !== "" || $ispirazione !== "") { ?>
  <section class="section panel panel--green reveal">
    <div class="section__head">
      <div>
        <h2><?= data_h($ispirazioneTitolo !== "" ? $ispirazioneTitolo : "Ispirazione") ?></h2>
      </div>
    </div>
    <?php if ($ispirazione !== "") { ?>
      <div class="prose">
        <?= data_nl2p($ispirazione) ?>
      </div>
    <?php } ?>
  </section>
<?php } ?>

<?php if ($conosciGuidoTitolo !== "" || $conosciGuido !== "") { ?>
  <section class="section panel panel--violet reveal">
    <div class="section__head">
      <div>
        <h2><?= data_h($conosciGuidoTitolo !== "" ? $conosciGuidoTitolo : "Conosci Guido?") ?></h2>
      </div>
    </div>
    <?php if ($conosciGuido !== "") { ?>
      <div class="prose">
        <?= data_nl2p($conosciGuido) ?>
      </div>
    <?php } ?>
  </section>
<?php } ?>
<?php
site_footer();
