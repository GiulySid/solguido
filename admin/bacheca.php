<?php

require __DIR__ . DIRECTORY_SEPARATOR . "lib.php";
admin_boot();
admin_require_login();
admin_reject_if_post_too_large("bacheca.php");

$error = "";

if (!function_exists("admin_bacheca_block")) {
    function admin_bacheca_block($title, $posts, $actions) {
        ob_start();
        echo '<section class="admin-section">';
        echo '<div class="admin-toolbar" style="margin-top:0">';
        echo '<p style="margin:0;font-weight:700">' . admin_h($title) . " · " . count($posts) . "</p>";
        echo "</div>";
        if (!$posts) {
            echo '<p class="admin-empty">Nessun messaggio.</p>';
        }
        foreach ($posts as $post) {
            $status = $post["status"] ?? "";
            $badgeClass = $status === "pending" ? "admin-badge--pending" : ($status === "hidden" ? "admin-badge--hidden" : "");
            $img = data_asset($post["immagineThumb"] ?: ($post["immagine"] ?? ""));
            echo '<article class="admin-item">';
            echo '<div class="admin-item__body">';
            if ($img !== "") {
                echo '<img class="admin-thumb" src="' . admin_h(site_asset($img)) . '" alt="" style="margin-bottom:0.5rem">';
            }
            echo "<h2>" . admin_h($post["autore"] ?? "-");
            if (!empty($post["fromGuido"])) {
                echo '<span class="admin-badge">Guido</span>';
            }
            echo '<span class="admin-badge ' . $badgeClass . '">' . admin_h(BACHECA_STATUS[$status] ?? $status) . "</span></h2>";
            echo "<p>" . admin_h(data_format_datetime($post["createdAt"] ?? "")) . "</p>";
            if (trim((string) ($post["testo"] ?? "")) !== "") {
                echo "<p>" . admin_h($post["testo"] ?? "") . "</p>";
            }
            echo "</div><div class=\"admin-item__actions\">";
            foreach ($actions as $action => $label) {
                $confirm = $action === "delete" ? ' data-confirm="Eliminare questo messaggio?"' : "";
                echo '<form method="post"' . $confirm . '>';
                echo admin_csrf_field();
                echo '<input type="hidden" name="id" value="' . admin_h($post["id"]) . '">';
                echo '<input type="hidden" name="action" value="' . admin_h($action) . '">';
                echo '<button class="admin-btn-ghost" type="submit">' . admin_h($label) . "</button>";
                echo "</form>";
            }
            echo "</div></article>";
        }
        echo "</section>";
        return ob_get_clean();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    admin_csrf_check();
    $action = (string) ($_POST["action"] ?? "");
    $posts = data_bacheca_posts(null);

    if ($action === "guido_post") {
        $testo = trim((string) ($_POST["testo"] ?? ""));
        $autore = trim((string) ($_POST["autore"] ?? "Guido"));
        if ($autore === "") {
            $autore = "Guido";
        }
        if ($testo === "") {
            $error = "Scrivi un messaggio.";
        } else {
            $imageRel = "";
            $thumbRel = "";
            if (!empty($_FILES["immagine"]) && (int) ($_FILES["immagine"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $basename = "guido-" . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 6);
                [$stored, $imgError] = admin_store_upload($_FILES["immagine"], "media/bacheca", $basename);
                if ($imgError) {
                    $error = $imgError;
                } elseif ($stored) {
                    $imageRel = $stored["src"];
                    $thumbRel = $stored["thumb"];
                }
            }
            if ($error === "") {
                $posts[] = [
                    "id" => data_new_id("guido"),
                    "autore" => $autore,
                    "testo" => data_ensure_guido_firma($testo),
                    "immagine" => $imageRel,
                    "immagineThumb" => $thumbRel,
                    "createdAt" => date("c"),
                    "status" => "approved",
                    "fromGuido" => true,
                ];
                if (!data_write("bacheca", ["posts" => array_values($posts)])) {
                    $error = "Impossibile salvare il post.";
                } else {
                    admin_flash("success", "Messaggio di Guido pubblicato.");
                    header("Location: bacheca.php");
                    exit;
                }
            }
        }
    } else {
        $id = (string) ($_POST["id"] ?? "");
        $index = null;
        foreach ($posts as $i => $post) {
            if (($post["id"] ?? "") === $id) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            admin_flash("error", "Post non trovato.");
            header("Location: bacheca.php");
            exit;
        }

        if ($action === "approve") {
            $posts[$index]["status"] = "approved";
            data_write("bacheca", ["posts" => array_values($posts)]);
            admin_flash("success", "Post approvato.");
        } elseif ($action === "hide") {
            $posts[$index]["status"] = "hidden";
            data_write("bacheca", ["posts" => array_values($posts)]);
            admin_flash("success", "Post nascosto.");
        } elseif ($action === "pending") {
            $posts[$index]["status"] = "pending";
            data_write("bacheca", ["posts" => array_values($posts)]);
            admin_flash("success", "Post rimesso in attesa.");
        } elseif ($action === "delete") {
            data_delete_rel($posts[$index]["immagine"] ?? "");
            data_delete_rel($posts[$index]["immagineThumb"] ?? "");
            array_splice($posts, $index, 1);
            data_write("bacheca", ["posts" => array_values($posts)]);
            admin_flash("success", "Post eliminato.");
        }

        header("Location: bacheca.php");
        exit;
    }
}

$pending = data_bacheca_posts(["pending"]);
$approved = data_bacheca_posts(["approved"]);
$hidden = data_bacheca_posts(["hidden"]);

ob_start();
?>
<a class="admin-back" href="index.php">← Contenuti</a>
<h1 class="admin-title">Bacheca</h1>
<p class="admin-lead">
  In coda: <strong><?= count($pending) ?></strong> da approvare ·
  <?= count($approved) ?> pubblicati ·
  <?= count($hidden) ?> nascosti.
</p>

<?php if ($error) { ?>
  <p class="admin-flash admin-flash--error" role="alert"><?= admin_h($error) ?></p>
<?php } ?>

<?= admin_bacheca_block("In attesa di approvazione", $pending, ["approve" => "Approva", "hide" => "Nascondi", "delete" => "Elimina"]) ?>

<section class="admin-section">
  <h2 style="font-family:var(--display);font-size:1.35rem;margin-bottom:0.75rem">Scrivi come Guido</h2>
  <form class="admin-form" method="post" enctype="multipart/form-data">
    <?= admin_csrf_field() ?>
    <input type="hidden" name="action" value="guido_post">
    <label class="admin-field">
      <span>Nome visualizzato</span>
      <input type="text" name="autore" value="Guido">
    </label>
    <label class="admin-field">
      <span>Messaggio</span>
      <textarea name="testo" required placeholder="Ciao…"><?= admin_h($_POST["testo"] ?? "") ?></textarea>
    </label>
    <label class="admin-field">
      <span>Immagine (opzionale)</span>
      <input type="file" name="immagine" accept="image/jpeg,image/png,image/webp,image/gif">
    </label>
    <div class="admin-actions">
      <button class="admin-btn" type="submit">Pubblica</button>
    </div>
  </form>
</section>

<?= admin_bacheca_block("Pubblicati", $approved, ["hide" => "Nascondi", "pending" => "Metti in attesa", "delete" => "Elimina"]) ?>
<?= admin_bacheca_block("Nascosti", $hidden, ["approve" => "Approva", "pending" => "Metti in attesa", "delete" => "Elimina"]) ?>
<?php
admin_layout("Bacheca", ob_get_clean(), ["nav" => true, "wide" => true]);
