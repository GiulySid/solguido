<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$cf = trim((string) ($site["codiceFiscale"] ?? ""));
site_header("sostienici", "Sostienici", "Destina l’8×1000 a SolGuido.");
?>
<header class="page-head reveal">
  <h1>8×1000</h1>
  <p>Un gesto concreto per sostenere musica, didattica e incontri sul territorio.</p>
</header>

<div class="form-board reveal">
  <aside class="support-card">
    <h2>Codice fiscale</h2>
    <p class="cf-box"><?= data_h($cf !== "" ? $cf : "XXXXXXXXXXXXXXX") ?></p>
    <p>Indica questo codice nello spazio 8×1000 della dichiarazione dei redditi.</p>
    <a class="btn-ghost" href="<?= data_h(site_asset("contatti")) ?>">Scrivici</a>
  </aside>
  <div class="prose">
    <?= data_nl2p($site["testo8x1000"] ?? "") ?>
  </div>
</div>
<?php
site_footer();
