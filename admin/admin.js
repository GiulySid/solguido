(() => {
  document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (window.tinymce) {
      window.tinymce.triggerSave();
    }
    const submitter = event.submitter;
    const message = submitter?.getAttribute("data-confirm") || form.getAttribute("data-confirm");
    if (message && !window.confirm(message)) {
      event.preventDefault();
    }
  });

  const richFields = document.querySelectorAll("textarea[data-richtext]");
  if (!richFields.length) return;

  const loadScript = (src) =>
    new Promise((resolve, reject) => {
      const existing = document.querySelector(`script[src="${src}"]`);
      if (existing) {
        if (window.tinymce) resolve();
        else existing.addEventListener("load", () => resolve(), { once: true });
        return;
      }
      const script = document.createElement("script");
      script.src = src;
      script.referrerPolicy = "origin";
      script.onload = () => resolve();
      script.onerror = () => reject(new Error("Impossibile caricare l’editor."));
      document.head.appendChild(script);
    });

  loadScript("https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js")
    .then(() => {
      if (!window.tinymce) return;
      window.tinymce.init({
        selector: "textarea[data-richtext]",
        license_key: "gpl",
        menubar: false,
        branding: false,
        promotion: false,
        plugins: "lists link autoresize",
        toolbar:
          "undo redo | styles | bold italic underline | fontsize | bullist numlist | link | removeformat",
        font_size_formats: "12px 14px 16px 18px 20px 24px 28px",
        style_formats: [
          { title: "Paragrafo", format: "p" },
          { title: "Titolo", format: "h3" },
          { title: "Sottotitolo", format: "h4" },
        ],
        link_default_target: "_blank",
        link_assume_external_targets: "https",
        convert_urls: false,
        relative_urls: false,
        skin: "oxide-dark",
        content_css: "dark",
        content_style:
          "body { font-family: Josefin Sans, system-ui, sans-serif; font-size: 16px; line-height: 1.5; } p { margin: 0 0 0.75em; }",
        min_height: 220,
        max_height: 560,
        language: "it",
        language_url: "https://cdn.jsdelivr.net/npm/tinymce-i18n@24.12.4/langs7/it.js",
        setup(editor) {
          editor.on("change input undo redo", () => editor.save());
        },
      });
    })
    .catch(() => {
      /* L’admin resta usabile con textarea semplice. */
    });
})();
