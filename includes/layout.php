<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "data.php";

function site_nav_items() {
    return [
        "home" => ["label" => "Home", "href" => site_asset("")],
        "chi-siamo" => ["label" => "Chi siamo", "href" => site_asset("chi-siamo")],
        "attivita" => ["label" => "Eventi", "href" => site_asset("attivita")],
        "bacheca" => ["label" => "Ricordi", "href" => site_asset("bacheca")],
        "sostienici" => ["label" => "Sostienici", "href" => site_asset("sostienici")],
        "contatti" => ["label" => "Contatti", "href" => site_asset("contatti")],
    ];
}

function site_social_icon_svg($network) {
    if ($network === "instagram") {
        return '<svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.7.1 4.8s0 3.6-.1 4.9c-.1 3.2-1.7 4.8-4.9 4.9-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9C2.4 3.9 4 2.3 7.1 2.2 8.4 2.2 8.8 2.2 12 2.2m0 1.8c-3.2 0-3.5 0-4.8.1-2.2.1-3.3 1.2-3.4 3.4-.1 1.2-.1 1.6-.1 4.7s0 3.5.1 4.7c.1 2.2 1.2 3.3 3.4 3.4 1.2.1 1.6.1 4.8.1s3.5 0 4.7-.1c2.2-.1 3.3-1.2 3.4-3.4.1-1.2.1-1.6.1-4.7s0-3.5-.1-4.7c-.1-2.2-1.2-3.3-3.4-3.4-1.2-.1-1.5-.1-4.7-.1m0 3.2a5.4 5.4 0 1 1 0 10.8 5.4 5.4 0 0 1 0-10.8m0 1.8a3.6 3.6 0 1 0 0 7.2 3.6 3.6 0 0 0 0-7.2m5.7-2.9a1.26 1.26 0 1 1-2.52 0 1.26 1.26 0 0 1 2.52 0"/></svg>';
    }
    if ($network === "facebook") {
        return '<svg class="social-icon social-icon--facebook" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M14.5 8.25h2.75V5.5H14.5c-2.35 0-4.25 1.9-4.25 4.25v1.75H7.5v2.75h2.75V19.5h2.75v-5.25h2.85l.65-2.75h-3.5V9.75c0-.55.35-1 .85-1z"/></svg>';
    }
    return "";
}

function site_social_links($modifier = "") {
    $site = data_site();
    $networks = [
        "instagram" => ["label" => "Instagram", "url" => trim((string) ($site["instagram"] ?? ""))],
        "facebook" => ["label" => "Facebook", "url" => trim((string) ($site["facebook"] ?? ""))],
    ];
    $links = [];
    foreach ($networks as $key => $item) {
        if ($item["url"] === "") {
            continue;
        }
        $links[] = '<a class="social-link social-link--' . data_h($key) . '" href="' . data_h($item["url"]) . '" target="_blank" rel="noopener noreferrer" aria-label="' . data_h($item["label"]) . '">' . site_social_icon_svg($key) . "</a>";
    }
    if (!$links) {
        return "";
    }
    $class = "social-links" . ($modifier !== "" ? " social-links--" . $modifier : "");
    return '<div class="' . data_h($class) . '">' . implode("", $links) . "</div>";
}

function site_header($current, $title, $description = "", $opts = []) {
    $site = data_site();
    $name = $site["nome"] ?: "SolGuido";
    $fullTitle = $current === "home" ? $name : ($title . " · " . $name);
    $description = $description !== "" ? $description : ($site["tagline"] ?: $name);
    $nav = site_nav_items();
    $root = dirname(__DIR__);
    $GLOBALS["site_css_v"] = (int) @filemtime($root . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "site.css");
    $GLOBALS["site_js_v"] = (int) @filemtime($root . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . "site.js");

    header("Content-Type: text/html; charset=utf-8");
    ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= data_h($fullTitle) ?></title>
  <meta name="description" content="<?= data_h($description) ?>">
  <?php if (!empty($opts["robots"])) { ?>
    <meta name="robots" content="<?= data_h($opts["robots"]) ?>">
  <?php } ?>
  <link rel="icon" href="<?= data_h(site_asset("img/favicon.png")) ?>" type="image/svg+xml">
  <link rel="apple-touch-icon" href="<?= data_h(site_asset("img/logo-mark.svg")) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=Josefin+Slab:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= data_h(site_asset("css/site.css")) ?>?v=<?= (int) ($GLOBALS["site_css_v"] ?? 0) ?>">
</head>
<body data-page="<?= data_h($current) ?>">
  <div class="sky" aria-hidden="true">
    <svg class="sky__mountains" viewBox="0 0 1440 320" preserveAspectRatio="none" focusable="false">
      <path class="sky__layer sky__layer--far" d="M0 220 L120 160 L240 210 L360 120 L480 190 L600 90 L720 170 L840 110 L960 200 L1080 130 L1200 185 L1320 140 L1440 190 L1440 320 L0 320 Z"></path>
      <path class="sky__layer sky__layer--mid" d="M0 250 L160 180 L300 240 L460 150 L620 230 L780 140 L940 220 L1100 160 L1280 230 L1440 175 L1440 320 L0 320 Z"></path>
      <path class="sky__layer sky__layer--near" d="M0 280 L200 210 L380 270 L560 190 L760 260 L980 200 L1180 265 L1440 220 L1440 320 L0 320 Z"></path>
    </svg>
  </div>

  <div class="nav-backdrop" data-nav-backdrop hidden></div>
  <?php if (empty($opts["hideNav"])) { ?>
    <nav id="site-nav" class="site-nav" aria-label="Principale">
      <div class="site-nav__panel">
        <div class="site-nav__top">
          <p class="site-nav__title">Menu</p>
          <button class="nav-close" type="button" data-nav-close aria-label="Chiudi menu">Chiudi</button>
        </div>
        <div class="site-nav__links">
          <?php foreach ($nav as $key => $item) { ?>
            <a href="<?= data_h($item["href"]) ?>"<?= $current === $key ? ' aria-current="page"' : "" ?>><?= data_h($item["label"]) ?></a>
          <?php } ?>
        </div>
      </div>
    </nav>
  <?php } ?>

  <header class="site-header">
    <a class="brand" href="<?= data_h(site_asset("")) ?>">
      <img class="brand__logo" src="<?= data_h(site_asset("img/logo.svg")) ?>" alt="<?= data_h($name) ?>" width="220" height="80">
    </a>
    <?php if (empty($opts["hideNav"])) { ?>
      <div class="site-header__tools">
        <div class="site-nav-desktop" aria-hidden="false">
          <?php foreach ($nav as $key => $item) { ?>
            <a href="<?= data_h($item["href"]) ?>"<?= $current === $key ? ' aria-current="page"' : "" ?>><?= data_h($item["label"]) ?></a>
          <?php } ?>
        </div>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
          <span class="nav-toggle__bars" aria-hidden="true"></span>
          <span class="nav-toggle__label">Menu</span>
        </button>
      </div>
    <?php } ?>
  </header>
  <main class="site-main" id="main">
    <?php
}

function site_footer() {
    $site = data_site();
    $name = $site["nome"] ?: "SolGuido";
    ?>
  </main>
  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <p class="footer-name"><?= data_h($site["nomeLegale"] ?: $name) ?></p>
        <p class="footer-tag"><?= data_h($site["tagline"] ?? "") ?></p>
        <?php if (!empty($site["sede"])) { ?>
          <p class="footer-tag"><?= data_h($site["sede"]) ?></p>
        <?php } ?>
        <?php if (!empty($site["codiceFiscale"])) { ?>
          <p class="footer-tag">CF <?= data_h($site["codiceFiscale"]) ?></p>
        <?php } ?>
      </div>
      <div class="footer-links">
        <a href="<?= data_h(site_asset("contatti")) ?>"><?= data_h($site["contattiTitolo"] ?: "Scrivici") ?></a>
        <?= site_social_links("footer") ?>
      </div>
    </div>
    <p class="footer-note">Sol Guido ODV</p>
  </footer>
  <script src="<?= data_h(site_asset("js/site.js")) ?>?v=<?= (int) ($GLOBALS["site_js_v"] ?? 0) ?>" defer></script>
</body>
</html>
    <?php
}

function site_attivita_meta($item) {
    $bits = [];
    $date = data_format_date($item["data"] ?? "");
    if ($date !== "") {
        $bits[] = $date;
    }
    if (!empty($item["ora"])) {
        $bits[] = (string) $item["ora"];
    }
    $place = trim(($item["luogo"] ?? "") . (!empty($item["citta"]) ? ", " . $item["citta"] : ""));
    if ($place !== "") {
        $bits[] = $place;
    }
    return implode(" · ", $bits);
}
