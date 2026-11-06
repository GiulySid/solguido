<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
site_header("chi-siamo", "Chi siamo", "Chi è SolGuido.");
?>
<header class="page-head reveal">
  <h1>Breve titolo per la sezione chi siamo</h1>
</header>
<div class="prose reveal">
  <?= data_nl2p($site["chiSiamo"] ?? "") ?>
</div>
<?php
site_footer();
