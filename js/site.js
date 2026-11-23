(() => {
  const toggle = document.querySelector(".nav-toggle");
  const nav = document.querySelector("#site-nav");
  const backdrop = document.querySelector("[data-nav-backdrop]");
  const header = document.querySelector(".site-header");
  const closeBtn = document.querySelector("[data-nav-close]");

  const setNavOpen = (open) => {
    if (!nav || !toggle) return;
    nav.classList.toggle("is-open", open);
    document.body.classList.toggle("nav-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    if (backdrop) {
      backdrop.hidden = !open;
    }
  };

  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      setNavOpen(!nav.classList.contains("is-open"));
    });
  }

  closeBtn?.addEventListener("click", () => setNavOpen(false));
  backdrop?.addEventListener("click", () => setNavOpen(false));

  nav?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => setNavOpen(false));
  });

  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") setNavOpen(false);
  });

  const onScroll = () => {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 8);
  };
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  const thread = document.querySelector("[data-chat-thread]");
  if (thread) {
    thread.scrollTop = thread.scrollHeight;
  }

  const chatInput = document.querySelector("[data-chat-input]");
  if (chatInput instanceof HTMLTextAreaElement) {
    const resize = () => {
      chatInput.style.height = "auto";
      chatInput.style.height = `${Math.min(chatInput.scrollHeight, 128)}px`;
    };
    chatInput.addEventListener("input", resize);
    resize();
  }

  const fileInput = document.querySelector("[data-chat-file]");
  const fileLabel = document.querySelector("[data-chat-filename]");
  if (fileInput instanceof HTMLInputElement && fileLabel) {
    fileInput.addEventListener("change", () => {
      const name = fileInput.files?.[0]?.name || "";
      if (!name) {
        fileLabel.hidden = true;
        fileLabel.textContent = "";
        return;
      }
      fileLabel.hidden = false;
      fileLabel.textContent = "Allegato: " + name;
    });
  }

  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const reveals = document.querySelectorAll(".reveal");
  if (reduceMotion) {
    reveals.forEach((el) => el.classList.add("is-visible"));
  } else if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.14, rootMargin: "0px 0px -8% 0px" }
    );
    reveals.forEach((el) => observer.observe(el));
  } else {
    reveals.forEach((el) => el.classList.add("is-visible"));
  }

  document.querySelectorAll("[data-expandable]").forEach((block) => {
    const body = block.querySelector("[data-expandable-body]");
    const btn = block.querySelector("[data-expandable-btn]");
    if (!(body instanceof HTMLElement) || !(btn instanceof HTMLButtonElement)) return;

    const measure = () => {
      if (block.classList.contains("is-open")) return;
      const needsToggle = body.scrollHeight > body.clientHeight + 12;
      btn.hidden = !needsToggle;
      if (!needsToggle) {
        block.classList.add("is-open");
      }
    };

    measure();
    window.addEventListener("resize", measure, { passive: true });

    btn.addEventListener("click", () => {
      const open = block.classList.toggle("is-open");
      btn.textContent = open ? "Mostra meno" : "Continua a leggere";
      if (!open) {
        measure();
      }
    });
  });

  const lightbox = document.querySelector("[data-gallery-lightbox]");
  const lightboxImg = document.querySelector("[data-gallery-image]");
  const lightboxCaption = document.querySelector("[data-gallery-caption]");
  const closeLightbox = () => {
    if (!(lightbox instanceof HTMLElement)) return;
    lightbox.hidden = true;
    document.body.classList.remove("gallery-open");
    if (lightboxImg instanceof HTMLImageElement) {
      lightboxImg.removeAttribute("src");
      lightboxImg.alt = "";
    }
  };

  document.querySelectorAll("[data-gallery-item]").forEach((link) => {
    if (!(link instanceof HTMLAnchorElement)) return;
    link.addEventListener("click", (event) => {
      if (!(lightbox instanceof HTMLElement) || !(lightboxImg instanceof HTMLImageElement)) return;
      event.preventDefault();
      lightboxImg.src = link.href;
      lightboxImg.alt = link.querySelector("img")?.alt || "";
      const caption = link.getAttribute("data-caption") || "";
      if (lightboxCaption instanceof HTMLElement) {
        lightboxCaption.textContent = caption;
        lightboxCaption.hidden = caption === "";
      }
      lightbox.hidden = false;
      document.body.classList.add("gallery-open");
    });
  });

  document.querySelector("[data-gallery-close]")?.addEventListener("click", closeLightbox);
  lightbox?.addEventListener("click", (event) => {
    if (event.target === lightbox) closeLightbox();
  });
  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeLightbox();
  });
})();