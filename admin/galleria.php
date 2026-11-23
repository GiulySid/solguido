<?php

require __DIR__ . DIRECTORY_SEPARATOR . "lib.php";
admin_boot();
admin_require_login();
admin_reject_if_post_too_large("galleria.php");

$error = "";
$gallery = data_galleria();
$items = $gallery["items"];
$titolo = $gallery["titolo"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    admin_csrf_check();
    $action = (string) ($_POST["action"] ?? "");

    if ($action === "save_title") {
        $titolo = trim((string) ($_POST["titolo"] ?? ""));
        if ($titolo === "") {
            $titolo = "Galleria";
        }
        if (!data_write("galleria", ["titolo" => $titolo, "items" => array_values($items)])) {
            $error = "Impossibile salvare il titolo.";
        } else {
            admin_flash("success", "Titolo galleria salvato.");
            header("Location: galleria.php");
            exit;
        }
    } elseif ($action === "upload") {
        $caption = trim((string) ($_POST["caption"] ?? ""));
        if (empty($_FILES["immagine"]) || (int) ($_FILES["immagine"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $error = "Scegli un’immagine da caricare.";
        } else {
            $basename = "foto-" . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 6);
            [$stored, $imgError] = admin_store_upload($_FILES["immagine"], "media/galleria", $basename);
            if ($imgError) {
                $error = $imgError;
            } elseif (!$stored) {
                $error = "Impossibile salvare l’immagine.";
            } else {
                $maxSort = 0;
                foreach ($items as $row) {
                    $maxSort = max($maxSort, (int) ($row["sort"] ?? 0));
                }
                $items[] = [
                    "id" => data_new_id("foto"),
                    "src" => $stored["src"],
                    "thumb" => $stored["thumb"],
                    "caption" => $caption,
                    "sort" => $maxSort + 1,
                    "createdAt" => date("c"),
                ];
                if (!data_write("galleria", ["titolo" => $titolo, "items" => array_values($items)])) {
                    $error = "Impossibile aggiornare la galleria.";
                } else {
                    admin_flash("success", "Foto aggiunta alla galleria.");
                    header("Location: galleria.php");
                    exit;
                }
            }
        }
    } elseif ($action === "delete") {
        $id = (string) ($_POST["id"] ?? "");
        $index = null;
        foreach ($items as $i => $row) {
            if (($row["id"] ?? "") === $id) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            admin_flash("error", "Foto non trovata.");
            header("Location: galleria.php");
            exit;
        }
        data_delete_rel($items[$index]["src"] ?? "");
        data_delete_rel($items[$index]["thumb"] ?? "");
        array_splice($items, $index, 1);
        if (!data_write("galleria", ["titolo" => $titolo, "items" => array_values($items)])) {
            $error = "Impossibile eliminare la foto.";
        } else {
            admin_flash("success", "Foto eliminata.");
            header("Location: galleria.php");
            exit;
        }
    } elseif ($action === "save_caption") {
        $id = (string) ($_POST["id"] ?? "");
        $caption = trim((string) ($_POST["caption"] ?? ""));
        $index = null;
        foreach ($items as $i => $row) {
            if (($row["id"] ?? "") === $id) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            admin_flash("error", "Foto non trovata.");
            header("Location: galleria.php");
            exit;
        }
        $items[$index]["caption"] = $caption;
        if (!data_write("galleria", ["titolo" => $titolo, "items" => array_values($items)])) {
            $error = "Impossibile salvare la didascalia.";
        } else {
            admin_flash("success", "Didascalia aggiornata.");
            header("Location: galleria.php");
            exit;
        }
    }
}

$gallery = data_galleria();
$items = $gallery["items"];
$titolo = $gallery["titolo"];

ob_start();
?>
<a class="admin-back" href="index.php">← Contenuti</a>
<h1 class="admin-title">Galleria</h1>
<p class="admin-lead">Foto mostrate in Chi siamo, dopo “Conosci Guido?”.</p>
<?php if ($error) { ?>
  <p class="admin-flash admin-flash--error" role="alert"><?= admin_h($error) ?></p>
<?php } ?>

<form class="admin-form" method="post" style="margin-bottom:2rem">
  <?= admin_csrf_field() ?>
  <input type="hidden" name="action" value="save_title">
  <label class="admin-field">
    <span>Titolo sezione</span>
    <input type="text" name="titolo" value="<?= admin_h($titolo) ?>">
  </label>
  <div class="admin-actions">
    <button class="admin-btn" type="submit">Salva titolo</button>
  </div>
</form>

<form class="admin-form" method="post" enctype="multipart/form-data" style="margin-bottom:2.5rem">
  <?= admin_csrf_field() ?>
  <input type="hidden" name="action" value="upload">
  <label class="admin-field">
    <span>Nuova foto</span>
    <input type="file" name="immagine" accept="image/jpeg,image/png,image/webp,image/gif" required>
    <span class="admin-help"><?= admin_h(admin_upload_limits_note()) ?></span>
  </label>
  <label class="admin-field">
    <span>Didascalia (opzionale)</span>
    <input type="text" name="caption" value="">
  </label>
  <div class="admin-actions">
    <button class="admin-btn" type="submit">Carica</button>
  </div>
</form>

<?php if (!$items) { ?>
  <p class="admin-empty">Nessuna foto in galleria.</p>
<?php } ?>

<?php foreach ($items as $item) {
    $thumb = data_asset($item["thumb"] ?: ($item["src"] ?? ""));
    $full = data_asset($item["src"] ?? "");
    ?>
  <article class="admin-item">
    <div class="admin-item__body" style="display:flex;gap:1rem;align-items:start">
      <?php if ($thumb !== "") { ?>
        <a href="<?= admin_h(site_asset($full !== "" ? $full : $thumb)) ?>" target="_blank" rel="noopener">
          <img class="admin-thumb" src="<?= admin_h(site_asset($thumb)) ?>" alt="" style="width:7rem;height:5.5rem">
        </a>
      <?php } ?>
      <form class="admin-form" method="post" style="flex:1;min-width:0">
        <?= admin_csrf_field() ?>
        <input type="hidden" name="action" value="save_caption">
        <input type="hidden" name="id" value="<?= admin_h($item["id"] ?? "") ?>">
        <label class="admin-field">
          <span>Didascalia</span>
          <input type="text" name="caption" value="<?= admin_h($item["caption"] ?? "") ?>">
        </label>
        <div class="admin-actions" style="margin:0">
          <button class="admin-btn-ghost" type="submit">Salva didascalia</button>
        </div>
      </form>
    </div>
    <div class="admin-item__actions">
      <form method="post" data-confirm="Eliminare questa foto?">
        <?= admin_csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= admin_h($item["id"] ?? "") ?>">
        <button class="admin-btn-ghost" type="submit">Elimina</button>
      </form>
    </div>
  </article>
<?php } ?>
<?php
admin_layout("Galleria", ob_get_clean(), ["nav" => true, "wide" => true]);
