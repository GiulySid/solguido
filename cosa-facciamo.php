<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$titolo = trim((string) ($site["cosaFacciamoTitolo"] ?? "")) ?: "Cosa facciamo";
$lead = trim((string) ($site["cosaFacciamoLead"] ?? ""));
site_header("cosa-facciamo", $titolo, $lead !== "" ? $lead : $titolo);
?>
<header class="page-head reveal">
  <h1><?= data_h($titolo) ?></h1>
  <?php if ($lead !== "") { ?>
    <p><?= data_h($lead) ?></p>
  <?php } ?>
</header>
<div class="prose reveal">
  <?= data_nl2p($site["cosaFacciamo"] ?? "") ?>
</div>
<section class="cta-band reveal">
  <h2><?= data_h($site["attivitaTitolo"] ?: "Prossimi eventi") ?></h2>
  <a class="btn" href="<?= data_h(site_asset("attivita")) ?>?tab=future">Vai al calendario</a>
</section>
<?php
site_footer();
