import fs from "fs";
import sharp from "sharp";

const full = fs.readFileSync("img/logo.svg", "utf8");

function withView(svg, viewBox, aria) {
  return svg
    .replace(/viewBox="[^"]*"/, `viewBox="${viewBox}"`)
    .replace(/\swidth="[^"]*"/, "")
    .replace(/\sheight="[^"]*"/, "")
    .replace(/aria-label="[^"]*"/, `aria-label="${aria}"`)
    .replace(/fill="currentColor"/g, 'fill="#2C2118"')
    .replace(/fill="#1a1a1a"/gi, 'fill="#2C2118"');
}

// Botanical clef only — tuned against the traced 1024x963 artwork
const markView = "335 5 245 720";
const favView = "325 0 265 740";

const logo = full
  .replace(/fill="currentColor"/g, 'fill="#2C2118"')
  .replace(/fill="#1a1a1a"/gi, 'fill="#2C2118"')
  .replace(/\swidth="[^"]*"/, "")
  .replace(/\sheight="[^"]*"/, "");

const mark = withView(full, markView, "Sol Guido");
const fav = withView(full, favView, "Sol Guido");

fs.writeFileSync("img/logo.svg", logo.endsWith("\n") ? logo : logo + "\n");
fs.writeFileSync("img/logo-mark.svg", mark.endsWith("\n") ? mark : mark + "\n");
fs.writeFileSync("img/favicon.svg", fav.endsWith("\n") ? fav : fav + "\n");

for (const [name, svg] of [
  ["logo", logo],
  ["logo-mark", mark],
  ["favicon", fav],
]) {
  await sharp(Buffer.from(svg))
    .flatten({ background: "#FFF5E6" })
    .resize(900)
    .png()
    .toFile(`img/${name}-preview.png`);
}

console.log("cropped mark/favicon viewBoxes");
