import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import sharp from "sharp";
import potrace from "potrace";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");
const src = path.join(root, "img", "logo-source.jpg");
const outFull = path.join(root, "img", "logo.svg");
const outMark = path.join(root, "img", "logo-mark.svg");
const outFavicon = path.join(root, "img", "favicon.png");

function trace(bufferOrPath, opts = {}) {
  return new Promise((resolve, reject) => {
    potrace.trace(
      bufferOrPath,
      {
        color: "#1a1a1a",
        background: "transparent",
        threshold: opts.threshold ?? 168,
        turdSize: opts.turdSize ?? 10,
        optTolerance: opts.optTolerance ?? 0.28,
        turnPolicy: "minority",
        ...opts,
      },
      (err, svg) => (err ? reject(err) : resolve(svg))
    );
  });
}

function cleanupSvg(svg, { aria = "Sol Guido" } = {}) {
  let out = svg
    .replace(/<\?xml[^>]*>\s*/i, "")
    .replace(/\sfill="#[0-9a-fA-F]+"/g, ' fill="currentColor"')
    .replace(/\sstroke="none"/g, "")
    .replace(/\sversion="1\.1"/g, "")
    .trim();

  if (!out.includes("xmlns=")) {
    out = out.replace("<svg ", '<svg xmlns="http://www.w3.org/2000/svg" ');
  }
  out = out.replace(
    "<svg ",
    `<svg role="img" aria-label="${aria}" `
  );
  return out + "\n";
}

async function monoPng(input, extract) {
  let pipeline = sharp(input);
  if (extract) pipeline = pipeline.extract(extract);
  return pipeline.greyscale().normalize().threshold(168).png().toBuffer();
}

const meta = await sharp(src).metadata();
const w = meta.width;
const h = meta.height;

// Full logo
const fullBuf = await monoPng(src);
fs.writeFileSync(outFull, cleanupSvg(await trace(fullBuf, { turdSize: 8 })));

// Central botanical clef only (between "Sol" and "Guido")
const markExtract = {
  left: Math.round(w * 0.28),
  top: Math.round(h * 0.0),
  width: Math.round(w * 0.34),
  height: Math.round(h * 0.92),
};
const markBuf = await monoPng(src, markExtract);
const markSvg = cleanupSvg(await trace(markBuf, { turdSize: 4 }), {
  aria: "Sol Guido",
});
fs.writeFileSync(outMark, markSvg);

// Square favicon from mark with padding
const markMeta = await sharp(markBuf).metadata();
const side = Math.max(markMeta.width, markMeta.height);
const pad = Math.round(side * 0.08);
const canvas = side + pad * 2;
const favBuf = await sharp({
  create: {
    width: canvas,
    height: canvas,
    channels: 4,
    background: { r: 255, g: 255, b: 255, alpha: 0 },
  },
})
  .composite([
    {
      input: markBuf,
      left: Math.floor((canvas - markMeta.width) / 2),
      top: Math.floor((canvas - markMeta.height) / 2),
    },
  ])
  .png()
  .toBuffer();

fs.writeFileSync(
  outFavicon,
  cleanupSvg(await trace(favBuf, { turdSize: 4 }), { aria: "Sol Guido" })
);

// Previews on cream for visual QA
for (const [name, file] of [
  ["logo", outFull],
  ["logo-mark", outMark],
  ["favicon", outFavicon],
]) {
  const svg = fs.readFileSync(file);
  await sharp(svg)
    .flatten({ background: "#FFF5E6" })
    .resize(900)
    .png()
    .toFile(path.join(root, "img", `${name}-preview.png`));
}

console.log(`Source ${w}x${h}`);
console.log("Wrote", outFull);
console.log("Wrote", outMark);
console.log("Wrote", outFavicon);
