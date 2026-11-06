<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$id = trim((string) ($_GET["id"] ?? ""));
$item = $id !== "" ? data_attivita_item($id) : null;

if (!$item) {
    http_response_code(404);
    site_header("attivita", "Attività non trovata");
    echo '<header class="page-head"><h1>Non trovata</h1><p>Questa attività non è disponibile.</p><a class="btn" href="attivita">Torna all’elenco</a></header>';
    site_footer();
    exit;
}

$img = data_asset($item["immagine"] ?? "");
site_header("attivita", (string) ($item["titolo"] ?? "Attività"), site_attivita_meta($item));
?>
<header class="page-head">
  <p class="back-link"><a href="attivita">Attività</a></p>
  <h1><?= data_h($item["titolo"] ?? "") ?></h1>
  <p class="event-detail__meta"><?= data_h(site_attivita_meta($item)) ?></p>
</header>

<article class="event-detail">
  <?php if ($img !== "") { ?>
    <div class="event-detail__media">
      <img src="<?= data_h($img) ?>" alt="">
    </div>
  <?php } ?>
  <div class="prose">
    <?= data_nl2p($item["testo"] ?? "") ?>
  </div>
</article>
<?php
site_footer();
