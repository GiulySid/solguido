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
$galleria = data_galleria();
$galleriaItems = $galleria["items"];
$galleriaTitolo = $galleria["titolo"];

$sections = [
    [
        "title" => $associazioneTitolo !== "" ? $associazioneTitolo : "L’associazione",
        "body" => $associazione,
        "panel" => "panel--golden",
        "show" => $associazioneTitolo !== "" || $associazione !== "",
    ],
    [
        "title" => $ispirazioneTitolo !== "" ? $ispirazioneTitolo : "Ispirazione",
        "body" => $ispirazione,
        "panel" => "panel--tangerine",
        "show" => $ispirazioneTitolo !== "" || $ispirazione !== "",
    ],
    [
        "title" => $conosciGuidoTitolo !== "" ? $conosciGuidoTitolo : "Conosci Guido?",
        "body" => $conosciGuido,
        "panel" => "panel--cream",
        "show" => $conosciGuidoTitolo !== "" || $conosciGuido !== "",
    ],
];
?>
<header class="page-head reveal">
  <h1><?= data_h($titolo) ?></h1>
  <?php if ($lead !== "") { ?>
    <p><?= data_h($lead) ?></p>
  <?php } ?>
</header>

<?php foreach ($sections as $section) {
    if (empty($section["show"])) {
        continue;
    }
    ?>
  <section class="section panel <?= data_h($section["panel"]) ?> reveal">
    <div class="section__head">
      <div>
        <h2><?= data_h($section["title"]) ?></h2>
      </div>
    </div>
    <?php if ($section["body"] !== "") { ?>
      <div class="story-block" data-expandable>
        <div class="prose story-block__body" data-expandable-body>
          <?= data_rich($section["body"]) ?>
        </div>
        <button class="btn story-block__btn" type="button" data-expandable-btn hidden>
          Continua a leggere
        </button>
      </div>
    <?php } ?>
  </section>
<?php } ?>

<?php if ($galleriaItems) { ?>
  <section class="section panel panel--cream reveal" id="galleria">
    <div class="section__head">
      <div>
        <h2><?= data_h($galleriaTitolo) ?></h2>
      </div>
    </div>
    <div class="gallery-grid">
      <?php foreach ($galleriaItems as $item) {
          $src = data_asset($item["src"] ?? "");
          $thumb = data_asset($item["thumb"] ?: ($item["src"] ?? ""));
          if ($src === "" && $thumb === "") {
              continue;
          }
          $caption = trim((string) ($item["caption"] ?? ""));
          $href = site_asset($src !== "" ? $src : $thumb);
          $img = site_asset($thumb !== "" ? $thumb : $src);
          ?>
        <figure class="gallery-card">
          <a class="gallery-card__link" href="<?= data_h($href) ?>" data-gallery-item<?= $caption !== "" ? ' data-caption="' . data_h($caption) . '"' : "" ?>>
            <img src="<?= data_h($img) ?>" alt="<?= data_h($caption !== "" ? $caption : $galleriaTitolo) ?>" loading="lazy">
          </a>
          <?php if ($caption !== "") { ?>
            <figcaption><?= data_h($caption) ?></figcaption>
          <?php } ?>
        </figure>
      <?php } ?>
    </div>
  </section>
  <div class="gallery-lightbox" data-gallery-lightbox hidden>
    <button class="gallery-lightbox__close" type="button" data-gallery-close aria-label="Chiudi">×</button>
    <img src="" alt="" data-gallery-image>
    <p class="gallery-lightbox__caption" data-gallery-caption hidden></p>
  </div>
<?php } ?>
<?php
site_footer();
