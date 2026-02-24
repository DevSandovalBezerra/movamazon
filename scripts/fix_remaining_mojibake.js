const fs = require("fs");
const path = require("path");

const DRY_RUN = process.argv.includes("--dry-run");
const ROOT = path.join(__dirname, "..");
const REPORT_PATH = path.join(ROOT, "build", "utf8_audit_report.json");

if (!fs.existsSync(REPORT_PATH)) {
  console.error("Report not found:", REPORT_PATH);
  process.exit(1);
}

const report = JSON.parse(fs.readFileSync(REPORT_PATH, "utf8"));
const files = (report?.samples?.files_with_mojibake_markers || [])
  .map((p) => p.replace(/\\/g, "/"))
  .filter((p) => p.startsWith("frontend/"))
  .filter((p) => p.endsWith(".js") || p.endsWith(".php"));

// Narrow markers: catches mojibake artifacts without flagging valid accented words.
const MARKER_REGEX =
  /(?:Ã[\u0080-\u00BF]|Â[\u0080-\u00BF]|â[\u0080-\u00BF]{1,2}|�|Ãƒ|Ã‚|Ã°|Å|preferªncia|Vocª|vocª|nºmero|múltiplas|úteis|bancárioário)/g;
const CONTROL_REGEX = /[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g;

const directMap = [
  ["ÃƒÆ’Ã‚Â¢Ãƒâ€¦ââ‚¬Å“ÃƒÂ¢ââ€šÂ¬Ã‚Â¦", ""],
  ["ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã¢â‚¬Â¦Ã¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã‚Â¢Ã¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¦", ""],
  ["Preferªncia", "Preferência"],
  ["preferªncia", "preferência"],
  ["Sucesso! Ã°Å¸Å½€°", "Sucesso!"],
  ["VALIDAAÇÃO", "VALIDAÇÃO"],
  ["INICIALIZAAÇÃO", "INICIALIZAÇÃO"],
  ["Âºltimos", "últimos"],
  ["Ã‚ngulo", "Ângulo"],
];

function scoreText(text) {
  const markerCount = (text.match(MARKER_REGEX) || []).length;
  const controlCount = (text.match(CONTROL_REGEX) || []).length;
  return markerCount * 5 + controlCount * 20;
}

function decodeLatin1(text) {
  return Buffer.from(text, "latin1").toString("utf8");
}

function applyDirectMap(text) {
  let out = text;
  for (const [from, to] of directMap) {
    out = out.split(from).join(to);
  }
  return out;
}

function normalizeLine(line) {
  let best = applyDirectMap(line);
  let bestScore = scoreText(best);
  let current = line;

  // Up to two passes avoids over-decoding.
  for (let pass = 0; pass < 2; pass += 1) {
    const decoded = applyDirectMap(decodeLatin1(current));
    const decodedScore = scoreText(decoded);
    if (decodedScore < bestScore) {
      best = decoded;
      bestScore = decodedScore;
    }
    if (decoded === current) break;
    current = decoded;
  }

  return best;
}

function normalizeText(text) {
  const eol = text.includes("\r\n") ? "\r\n" : "\n";
  const lines = text.split(/\r?\n/);
  const fixed = lines.map((line) => normalizeLine(line));
  return fixed.join(eol);
}

let changedFiles = 0;
let totalScoreDelta = 0;

for (const rel of files) {
  const full = path.join(ROOT, rel);
  if (!fs.existsSync(full)) continue;

  const original = fs.readFileSync(full, "utf8");
  const fixed = normalizeText(original);

  const before = scoreText(original);
  const after = scoreText(fixed);

  if (fixed !== original && after <= before) {
    changedFiles += 1;
    totalScoreDelta += before - after;
    console.log(`${rel}: score ${before} -> ${after}`);
    if (!DRY_RUN) {
      fs.writeFileSync(full, fixed, "utf8");
    }
  }
}

console.log(`changed_files=${changedFiles}`);
console.log(`score_delta=${totalScoreDelta}`);
console.log(`dry_run=${DRY_RUN}`);
