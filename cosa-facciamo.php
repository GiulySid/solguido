<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
site_header("cosa-facciamo", "Cosa facciamo", "Le attività di SolGuido.");
?>
<header class="page-head reveal">
  <h1>Musica che unisce, anche in quota</h1>
  <p>Sottotitolo per la sezione cosa facciamo</p>
</header>
<div class="prose reveal">
  <?= data_nl2p($site["cosaFacciamo"] ?? "") ?>
</div>
<section class="cta-band reveal">
  <h2>Guarda le prossime attività</h2>
  <a class="btn" href="<?= data_h(site_asset("attivita")) ?>?tab=future">Vai alle attività</a>
</section>
<?php
site_footer();
