<?php

require __DIR__ . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "layout.php";

$site = data_site();
$sent = false;
$error = "";
$values = [
    "autore" => "",
    "testo" => "",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $values["autore"] = trim((string) ($_POST["autore"] ?? ""));
    $values["testo"] = trim((string) ($_POST["testo"] ?? ""));
    // Avoid common autofill names like "website" (they fake a success without saving).
    $honeypot = trim((string) ($_POST["sg_hp_field"] ?? ""));
    $hasFile = !empty($_FILES["immagine"]) && (int) ($_FILES["immagine"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if ($honeypot !== "") {
        // Silent discard for bots; do not show a fake "pending approval" message.
        $sent = false;
        $values = ["autore" => "", "testo" => ""];
    } elseif ($values["autore"] === "") {
        $error = "Indica il tuo nome.";
    } elseif (data_strlen($values["autore"]) > 80) {
        $error = "Il nome è troppo lungo.";
    } elseif ($values["testo"] === "" && !$hasFile) {
        $error = "Scrivi un messaggio o allega un’immagine.";
    } elseif (data_strlen($values["testo"]) > 2000) {
        $error = "Il messaggio è troppo lungo (max 2000 caratteri).";
    } else {
        $imageRel = "";
        $thumbRel = "";
        if ($hasFile) {
            $file = $_FILES["immagine"];
            if ((int) ($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $error = "Upload immagine non riuscito.";
            } elseif ((int) ($file["size"] ?? 0) > 8 * 1024 * 1024) {
                $error = "L’immagine supera gli 8 MB.";
            } else {
                $basename = "msg-" . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 6);
                [$stored, $imgError] = data_store_image($file["tmp_name"], "media/bacheca", $basename, 1600, 480);
                if ($imgError) {
                    $error = $imgError;
                } elseif ($stored) {
                    $imageRel = $stored["src"];
                    $thumbRel = $stored["thumb"];
                }
            }
        }

        if ($error === "") {
            $posts = data_bacheca_posts(null);
            $posts[] = [
                "id" => data_new_id("bacheca"),
                "autore" => $values["autore"],
                "testo" => $values["testo"],
                "immagine" => $imageRel,
                "immagineThumb" => $thumbRel,
                "createdAt" => date("c"),
                "status" => "pending",
                "fromGuido" => false,
            ];
            if (!data_write("bacheca", ["posts" => array_values($posts)])) {
                $error = "Impossibile salvare il messaggio. Riprova più tardi.";
                if ($imageRel !== "") {
                    data_delete_rel($imageRel);
                    data_delete_rel($thumbRel);
                }
            } else {
                $sent = true;
                $values = ["autore" => "", "testo" => ""];
            }
        }
    }
}

$posts = data_bacheca_posts(["approved"]);
$wallpaper = site_asset("img/placeholders/bacheca-wallpaper.jpg");
site_header("bacheca", "Bacheca", "Messaggi per SolGuido, come una chat con Guido.");
?>
<header class="page-head reveal">
  <h1>Bacheca</h1>
</header>

<div class="bacheca reveal">
  <div class="chat-shell" style="--chat-wallpaper: url('<?= data_h($wallpaper) ?>')">
    <div class="chat-thread" data-chat-thread>
      <?php if (!$posts) { ?>
        <div class="chat-bubble chat-bubble--guido">
          <div class="chat-bubble__meta"><span>Guido</span><span>presto</span></div>
          <div class="chat-bubble__text">Ancora nessun messaggio pubblico. Scrivi tu il primo pensiero.

<?= data_h(data_firma_guido()) ?></div>
        </div>
      <?php } ?>
      <?php foreach ($posts as $post) {
          $isGuido = !empty($post["fromGuido"]);
          $img = data_asset($post["immagineThumb"] ?: ($post["immagine"] ?? ""));
          $full = data_asset($post["immagine"] ?? "");
          ?>
        <article class="chat-bubble <?= $isGuido ? "chat-bubble--guido" : "chat-bubble--user" ?>">
          <div class="chat-bubble__meta">
            <span><?= data_h($post["autore"] ?? ($isGuido ? "Guido" : "Amico")) ?></span>
            <span><?= data_h(data_format_datetime($post["createdAt"] ?? "")) ?></span>
          </div>
          <?php if ($img !== "") { ?>
            <div class="chat-bubble__img">
              <?php if ($full !== "") { ?>
                <a href="<?= data_h($full) ?>" target="_blank" rel="noopener">
                  <img src="<?= data_h($img) ?>" alt="">
                </a>
              <?php } else { ?>
                <img src="<?= data_h($img) ?>" alt="">
              <?php } ?>
            </div>
          <?php } ?>
          <?php if (trim((string) ($post["testo"] ?? "")) !== "") { ?>
            <div class="chat-bubble__text"><?= data_h($post["testo"] ?? "") ?></div>
          <?php } ?>
        </article>
      <?php } ?>
    </div>

    <form class="chat-compose" method="post" enctype="multipart/form-data" data-chat-compose>
      <?php if ($sent) { ?>
        <p class="form-note form-note--ok">Grazie. Il tuo messaggio è in attesa di approvazione.</p>
      <?php } ?>
      <?php if ($error !== "") { ?>
        <p class="form-note form-note--err"><?= data_h($error) ?></p>
      <?php } ?>
      <label class="hp" for="sg_hp_field">Azienda
        <input type="text" id="sg_hp_field" name="sg_hp_field" tabindex="-1" autocomplete="off">
      </label>
      <label class="chat-compose__name">
        <span class="visually-hidden">Il tuo nome</span>
        <input type="text" name="autore" required maxlength="80" placeholder="Il tuo nome" value="<?= data_h($values["autore"]) ?>">
      </label>
      <div class="chat-compose__bar">
        <label class="chat-compose__attach" title="Allega immagine">
          <span class="visually-hidden">Immagine</span>
          <input type="file" name="immagine" accept="image/jpeg,image/png,image/webp,image/gif" data-chat-file>
          <span class="chat-compose__attach-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21.44 11.05l-8.49 8.49a5.5 5.5 0 01-7.78-7.78l9.19-9.19a3.5 3.5 0 014.95 4.95l-9.2 9.19a1.5 1.5 0 01-2.12-2.12l8.49-8.48"/>
            </svg>
          </span>
        </label>
        <label class="chat-compose__message">
          <span class="visually-hidden">Messaggio</span>
          <textarea name="testo" maxlength="2000" rows="1" placeholder="Scrivi un messaggio" data-chat-input><?= data_h($values["testo"]) ?></textarea>
        </label>
        <button class="chat-compose__send" type="submit" aria-label="Invia">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
          </svg>
        </button>
      </div>
      <p class="chat-compose__file" data-chat-filename hidden></p>
    </form>
  </div>
</div>
<?php
site_footer();
