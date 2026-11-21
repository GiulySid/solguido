<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$items = data_attivita();
$today = date("Y-m-d");
$future = [];
$past = [];

foreach ($items as $item) {
    $day = trim((string) ($item["data"] ?? ""));
    if ($day !== "" && $day < $today) {
        $past[] = $item;
    } else {
        $future[] = $item;
    }
}

usort($future, static function ($a, $b) {
    return ((string) ($a["data"] ?? "")) <=> ((string) ($b["data"] ?? ""));
});
usort($past, static function ($a, $b) {
    return ((string) ($b["data"] ?? "")) <=> ((string) ($a["data"] ?? ""));
});

$tab = trim((string) ($_GET["tab"] ?? "future"));
if ($tab !== "past" && $tab !== "future") {
    $tab = "future";
}
if ($tab === "future" && !$future && $past) {
    $tab = "past";
}
if ($tab === "past" && !$past && $future) {
    $tab = "future";
}

$shown = $tab === "past" ? $past : $future;

$site = data_site();
$titolo = trim((string) ($site["attivitaTitolo"] ?? "")) ?: "Prossimi eventi";
$lead = trim((string) ($site["attivitaLead"] ?? ""));
site_header("attivita", $titolo, $lead !== "" ? $lead : $titolo);
?>
<header class="page-head reveal">
  <h1><?= data_h($titolo) ?></h1>
  <?php if ($lead !== "") { ?>
    <p><?= data_h($lead) ?></p>
  <?php } ?>
</header>

<?php if (!$items) { ?>
  <p class="prose reveal">Nessuna attività pubblicata per ora. Torna a trovarci presto.</p>
<?php } else { ?>
  <div class="seg-control reveal" role="tablist" aria-label="Filtro attività">
    <a class="seg-control__btn<?= $tab === "future" ? " is-active" : "" ?>" href="attivita?tab=future" role="tab" aria-selected="<?= $tab === "future" ? "true" : "false" ?>">
      Prossimi <span><?= count($future) ?></span>
    </a>
    <a class="seg-control__btn<?= $tab === "past" ? " is-active" : "" ?>" href="attivita?tab=past" role="tab" aria-selected="<?= $tab === "past" ? "true" : "false" ?>">
      Passati <span><?= count($past) ?></span>
    </a>
  </div>

  <?php if (!$shown) { ?>
    <p class="prose reveal"><?= $tab === "past" ? "Nessuna attività passata per ora." : "Nessuna attività in programma per ora." ?></p>
  <?php } else { ?>
    <div class="event-list">
      <?php foreach ($shown as $item) {
          $dt = date_create((string) ($item["data"] ?? ""));
          $day = $dt ? $dt->format("j") : "-";
          $month = $dt ? $dt->format("M") : "";
          ?>
        <a class="event-card reveal" href="attivita-dettaglio?id=<?= data_h($item["id"]) ?>">
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
  <?php } ?>
<?php } ?>
<?php
site_footer();
