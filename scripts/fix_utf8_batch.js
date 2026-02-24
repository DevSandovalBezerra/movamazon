/**
 * Batch UTF-8/mojibake fixer for frontend source files.
 *
 * Usage:
 *   node scripts/fix_utf8_batch.js [--dry-run] [--phase=N]
 *
 * Flags:
 *   --dry-run   Only report planned changes, do not write files.
 *   --phase=N   Process phase 1..5. Omit to process all frontend JS/PHP.
 */

const fs = require("fs");
const path = require("path");

const DRY_RUN = process.argv.includes("--dry-run");
const PHASE_ARG = process.argv.find((arg) => arg.startsWith("--phase="));
const PHASE = PHASE_ARG ? Number(PHASE_ARG.split("=")[1]) : 0;

const ROOT = path.join(__dirname, "..");
const FRONTEND = path.join(ROOT, "frontend");

const MARKER_REGEX =
  /(?:\u00C3[\u0080-\u024F]|\u00C2[\u0080-\u024F]|\u00E2[\u0080-\u024F]{1,3}|�|prefer\u00AAncia|Voc\u00AA|voc\u00AA|v\u00AAm|n\u00BAmero|m\u00BAltiplas|\u00BAteis|banc\u00E1rio\u00E1rio)/u;

const SCORE_PATTERNS = [
  { regex: /\u00C3[\u0080-\u024F]/gu, weight: 5 },
  { regex: /\u00C2[\u0080-\u024F]/gu, weight: 5 },
  { regex: /\u00E2[\u0080-\u024F]{1,3}/gu, weight: 4 },
  { regex: /�/g, weight: 20 },
  { regex: /[ªº]/g, weight: 1 },
];

const NBSP = "\u00A0";
const GLITCH_A = "\u00E2\u00E2\u201A\u00AC\u00E2\u20AC\u201E\u00A2";

const DIRECT_REPLACEMENTS = [
  [new RegExp(NBSP, "g"), " "],
  [/prefer\u00AAncia/g, "preferência"],
  [/Voc\u00AA/g, "Você"],
  [/voc\u00AA/g, "você"],
  [/v\u00AAm/g, "vêm"],
  [/n\u00BAmero/g, "número"],
  [/N\u00BAmero/g, "Número"],
  [/m\u00BAltiplas/g, "múltiplas"],
  [/\u00BAteis/g, "úteis"],
  [/banc\u00E1rio\u00E1rio/g, "bancário"],
  [new RegExp(`PADR ?${GLITCH_A}O`, "g"), "PADRÃO"],
  [new RegExp(`CONFIGURA ?${GLITCH_A}O`, "g"), "CONFIGURAÇÃO"],
  [new RegExp(`CORRE ?${GLITCH_A}O`, "g"), "CORREÇÃO"],
  [new RegExp(`N ?${GLITCH_A}O`, "g"), "NÃO"],
  [new RegExp(`ATEN ?${GLITCH_A}O`, "g"), "ATENÇÃO"],
];

function scoreText(text) {
  let score = 0;
  for (const { regex, weight } of SCORE_PATTERNS) {
    const matches = text.match(regex);
    if (matches) score += matches.length * weight;
  }
  return score;
}

const CP1252_UNICODE_TO_BYTE = new Map([
  [0x20ac, 0x80],
  [0x201a, 0x82],
  [0x0192, 0x83],
  [0x201e, 0x84],
  [0x2026, 0x85],
  [0x2020, 0x86],
  [0x2021, 0x87],
  [0x02c6, 0x88],
  [0x2030, 0x89],
  [0x0160, 0x8a],
  [0x2039, 0x8b],
  [0x0152, 0x8c],
  [0x017d, 0x8e],
  [0x2018, 0x91],
  [0x2019, 0x92],
  [0x201c, 0x93],
  [0x201d, 0x94],
  [0x2022, 0x95],
  [0x2013, 0x96],
  [0x2014, 0x97],
  [0x02dc, 0x98],
  [0x2122, 0x99],
  [0x0161, 0x9a],
  [0x203a, 0x9b],
  [0x0153, 0x9c],
  [0x017e, 0x9e],
  [0x0178, 0x9f],
]);

function cp1252ToUtf8(text) {
  const bytes = [];
  for (let i = 0; i < text.length; i += 1) {
    const code = text.charCodeAt(i);
    if (code <= 0xff) {
      bytes.push(code);
      continue;
    }
    const mapped = CP1252_UNICODE_TO_BYTE.get(code);
    if (mapped === undefined) {
      return text;
    }
    bytes.push(mapped);
  }
  return Buffer.from(bytes).toString("utf8");
}

function applyDirectReplacements(text) {
  let out = text;
  for (const [fromRegex, to] of DIRECT_REPLACEMENTS) {
    out = out.replace(fromRegex, to);
  }
  return out;
}

function normalizeLine(line) {
  const direct = applyDirectReplacements(line);
  let best = direct;
  let bestScore = scoreText(direct);

  if (!MARKER_REGEX.test(line)) return best;

  let current = line;
  for (let pass = 0; pass < 4; pass += 1) {
    const next = cp1252ToUtf8(current);
    if (next === current) break;
    current = next;

    const candidate = applyDirectReplacements(current);
    const score = scoreText(candidate);
    if (score < bestScore) {
      best = candidate;
      bestScore = score;
    }
  }

  return best;
}

function normalizeText(text) {
  const eol = text.includes("\r\n") ? "\r\n" : "\n";
  const lines = text.split(/\r?\n/);
  const fixed = lines.map((line) => normalizeLine(line));
  return fixed.join(eol);
}

function collectFiles(dir, base = "", out = []) {
  if (!fs.existsSync(dir)) return out;
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const rel = path.join(base, entry.name);
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      if (entry.name === "node_modules" || entry.name === "inscricao_EXEMPLO") continue;
      collectFiles(full, rel, out);
      continue;
    }
    if (!entry.isFile()) continue;
    if (!entry.name.endsWith(".js") && !entry.name.endsWith(".php")) continue;
    if (entry.name.endsWith(".min.js")) continue;
    out.push(path.relative(ROOT, full).replace(/\\/g, "/"));
  }
  return out;
}

function listFilesPhase(phase) {
  const list = [];

  if (phase === 1) {
    const dirs = [
      path.join(FRONTEND, "paginas", "inscricao"),
      path.join(FRONTEND, "paginas", "inscricao", "includes"),
      path.join(FRONTEND, "paginas", "participante"),
      path.join(FRONTEND, "js", "inscricao"),
      path.join(FRONTEND, "js", "participante"),
    ];
    for (const dir of dirs) collectFiles(dir, path.relative(ROOT, dir), list);
    return [...new Set(list)];
  }

  if (phase === 2) {
    collectFiles(path.join(FRONTEND, "paginas", "participante"), "frontend/paginas/participante", list);
    collectFiles(path.join(FRONTEND, "paginas", "auth"), "frontend/paginas/auth", list);
    collectFiles(path.join(FRONTEND, "js", "participante"), "frontend/js/participante", list);
    for (const name of ["auth-register.js", "auth-handler.js"]) {
      const full = path.join(FRONTEND, "js", name);
      if (fs.existsSync(full)) list.push(path.relative(ROOT, full).replace(/\\/g, "/"));
    }
    return [...new Set(list)];
  }

  if (phase === 3) {
    for (const segment of [
      ["paginas", "admin"],
      ["paginas", "organizador"],
      ["paginas", "public"],
      ["js", "admin"],
      ["js", "organizador"],
      ["js", "public"],
    ]) {
      collectFiles(path.join(FRONTEND, ...segment), path.join("frontend", ...segment), list);
    }
    for (const name of ["eventos.js", "programacao.js", "organizador-eventos.js", "organizador-criar-evento.js"]) {
      const full = path.join(FRONTEND, "js", name);
      if (fs.existsSync(full)) list.push(path.relative(ROOT, full).replace(/\\/g, "/"));
    }
    return [...new Set(list)];
  }

  if (phase === 4) {
    collectFiles(path.join(FRONTEND, "includes"), "frontend/includes", list);
    return [...new Set(list)];
  }

  if (phase === 5) {
    for (const segment of [["js", "utils"], ["js", "api"], ["js", "components"], ["js"]]) {
      collectFiles(path.join(FRONTEND, ...segment), path.join("frontend", ...segment), list);
    }
    return [...new Set(list)];
  }

  collectFiles(path.join(FRONTEND, "paginas"), "frontend/paginas", list);
  collectFiles(path.join(FRONTEND, "includes"), "frontend/includes", list);
  collectFiles(path.join(FRONTEND, "js"), "frontend/js", list);
  return [...new Set(list)];
}

function getFiles() {
  if (PHASE >= 1 && PHASE <= 5) return listFilesPhase(PHASE);
  return listFilesPhase(0);
}

function main() {
  const files = getFiles();
  let changedFiles = 0;
  let touchedLines = 0;
  const log = [];

  for (const rel of files) {
    const full = path.join(ROOT, rel);
    if (!fs.existsSync(full)) continue;

    let original;
    try {
      original = fs.readFileSync(full, "utf8");
    } catch (err) {
      log.push(`${rel}: read_error=${err.message}`);
      continue;
    }

    const normalized = normalizeText(original);
    if (normalized === original) continue;

    const beforeScore = scoreText(original);
    const afterScore = scoreText(normalized);
    if (afterScore >= beforeScore) continue;

    changedFiles += 1;
    touchedLines += Math.abs(original.split(/\r?\n/).length - normalized.split(/\r?\n/).length);
    log.push(`${rel}: score ${beforeScore} -> ${afterScore}`);

    if (!DRY_RUN) {
      fs.writeFileSync(full, normalized, "utf8");
    }
  }

  console.log(DRY_RUN ? "[DRY-RUN]" : "[APPLY]");
  console.log(`phase=${PHASE || "all"}`);
  console.log(`changed_files=${changedFiles}`);
  console.log(`line_delta=${touchedLines}`);
  for (const line of log) console.log(line);
}

main();
