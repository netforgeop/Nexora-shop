/**
 * Minimal, dependency-free template engine used by scripts/build.mjs.
 *
 * Syntax (Mustache/Handlebars flavoured):
 *   {{path.to.value}}            escaped output
 *   {{{path.to.value}}}          raw HTML output
 *   {{> partialName}}            include a partial (same context)
 *   {{#if expr}}…{{else}}…{{/if}} expr = path | !path | path == "str" | path != "str"
 *   {{#each path}}…{{/each}}      iterate arrays/objects; inside: {{this}}, {{this.x}}, {{@index}}, {{@first}}, {{@last}}, {{@key}}
 *   {{#with path}}…{{/with}}      scope helper
 *   {{!-- comment --}}
 *   {{t "dotted.key" n=5 name=path}}  i18n helper -> ctx.t(key, params)
 */

const OPEN = '{{';
const CLOSE = '}}';

/** Wrapper for helper output that is already HTML and must not be escaped. */
export class SafeString {
  constructor(s) { this.s = s == null ? '' : String(s); }
  toString() { return this.s; }
}
export const raw = (s) => (s instanceof SafeString ? s : new SafeString(s));

export function compile(source, { partials = {}, helpers = {} } = {}) {
  const ast = parse(tokenize(source));
  return (ctx) => render(ast, [ctx], { partials, helpers, ctx });
}

/* ---------------- tokenizer ---------------- */
function tokenize(src) {
  const tokens = [];
  let i = 0;
  while (i < src.length) {
    const start = src.indexOf(OPEN, i);
    if (start === -1) { tokens.push({ type: 'text', value: src.slice(i) }); break; }
    if (start > i) tokens.push({ type: 'text', value: src.slice(i, start) });
    const raw = src.startsWith('{{{', start);
    const closeTag = raw ? '}}}' : CLOSE;
    const end = src.indexOf(closeTag, start + (raw ? 3 : 2));
    if (end === -1) throw new Error(`Unclosed tag near: ${src.slice(start, start + 40)}`);
    const inner = src.slice(start + (raw ? 3 : 2), end).trim();
    i = end + closeTag.length;
    if (inner.startsWith('!')) continue; // comment
    if (raw) { tokens.push({ type: 'raw', expr: inner }); continue; }
    if (inner.startsWith('>')) { tokens.push({ type: 'partial', name: inner.slice(1).trim() }); continue; }
    if (inner.startsWith('#')) {
      const [kw, ...rest] = inner.slice(1).trim().split(/\s+/);
      tokens.push({ type: 'open', kw, expr: rest.join(' ') });
      continue;
    }
    if (inner.startsWith('/')) { tokens.push({ type: 'close', kw: inner.slice(1).trim() }); continue; }
    if (inner === 'else') { tokens.push({ type: 'else' }); continue; }
    tokens.push({ type: 'var', expr: inner });
  }
  return tokens;
}

/* ---------------- parser ---------------- */
function parse(tokens) {
  let pos = 0;
  function block(closeKw) {
    const nodes = [];
    let elseNodes = null;
    while (pos < tokens.length) {
      const tok = tokens[pos++];
      if (tok.type === 'close') {
        if (tok.kw !== closeKw) throw new Error(`Mismatched close {{/${tok.kw}}} (expected {{/${closeKw}}})`);
        return { nodes, elseNodes };
      }
      if (tok.type === 'else') { elseNodes = []; continue; }
      const target = elseNodes || nodes;
      if (tok.type === 'open') {
        const inner = block(tok.kw);
        target.push({ type: tok.kw, expr: tok.expr, nodes: inner.nodes, elseNodes: inner.elseNodes });
      } else {
        target.push(tok);
      }
    }
    if (closeKw) throw new Error(`Unclosed block {{#${closeKw}}}`);
    return { nodes, elseNodes };
  }
  return block(null).nodes;
}

/* ---------------- renderer ---------------- */
function render(nodes, scopes, env) {
  let out = '';
  for (const n of nodes) {
    switch (n.type) {
      case 'text': out += n.value; break;
      case 'var': { const v = evalExpr(n.expr, scopes, env); out += v instanceof SafeString ? v.s : escapeHtml(stringify(v)); break; }
      case 'raw': out += stringify(evalExpr(n.expr, scopes, env)); break;
      case 'partial': {
        const p = env.partials[n.name];
        if (!p) throw new Error(`Unknown partial: ${n.name}`);
        out += p(scopes[scopes.length - 1], scopes, env);
        break;
      }
      case 'if': {
        const ok = truthy(evalCondition(n.expr, scopes, env));
        out += ok ? render(n.nodes, scopes, env) : (n.elseNodes ? render(n.elseNodes, scopes, env) : '');
        break;
      }
      case 'unless': {
        const ok = !truthy(evalCondition(n.expr, scopes, env));
        out += ok ? render(n.nodes, scopes, env) : (n.elseNodes ? render(n.elseNodes, scopes, env) : '');
        break;
      }
      case 'each': {
        const val = evalExpr(n.expr, scopes, env);
        const entries = Array.isArray(val) ? val.map((v, i) => [i, v]) : val && typeof val === 'object' ? Object.entries(val) : [];
        if (!entries.length) { if (n.elseNodes) out += render(n.elseNodes, scopes, env); break; }
        entries.forEach(([key, item], idx) => {
          const frame = { this: item, '@index': idx, '@key': key, '@first': idx === 0, '@last': idx === entries.length - 1, '@number': idx + 1 };
          out += render(n.nodes, [...scopes, frame], env);
        });
        break;
      }
      case 'with': {
        const val = evalExpr(n.expr, scopes, env);
        out += render(n.nodes, [...scopes, { this: val }], env);
        break;
      }
      default: throw new Error(`Unknown node ${n.type}`);
    }
  }
  return out;
}

function evalCondition(expr, scopes, env) {
  const m = expr.match(/^(.+?)\s*(==|!=)\s*(.+)$/);
  if (m) {
    const a = evalExpr(m[1].trim(), scopes, env);
    const b = evalExpr(m[3].trim(), scopes, env);
    return m[2] === '==' ? a == b : a != b; // eslint-disable-line eqeqeq
  }
  if (expr.startsWith('!')) return !truthy(evalExpr(expr.slice(1).trim(), scopes, env));
  return evalExpr(expr, scopes, env);
}

function evalExpr(expr, scopes, env) {
  expr = expr.trim();
  // helper call: t "key" a=1 b=path
  const helperMatch = expr.match(/^([a-zA-Z_][\w]*)\s+(.+)$/);
  if (helperMatch && env.helpers[helperMatch[1]]) {
    const args = parseArgs(helperMatch[2], scopes, env);
    return env.helpers[helperMatch[1]](...args.positional, args.named, scopes[scopes.length - 1]);
  }
  return lookup(expr, scopes);
}

function parseArgs(str, scopes, env) {
  const positional = [];
  const named = {};
  const re = /(\w+)=("[^"]*"|'[^']*'|[^\s]+)|("[^"]*"|'[^']*'|[^\s]+)/g;
  let m;
  while ((m = re.exec(str))) {
    if (m[1]) named[m[1]] = literalOrLookup(m[2], scopes);
    else positional.push(literalOrLookup(m[3], scopes));
  }
  return { positional, named };
}

function literalOrLookup(token, scopes) {
  if (/^["'].*["']$/.test(token)) return token.slice(1, -1);
  if (/^-?\d+(\.\d+)?$/.test(token)) return Number(token);
  if (token === 'true') return true;
  if (token === 'false') return false;
  return lookup(token, scopes);
}

function lookup(path, scopes) {
  if (/^["'].*["']$/.test(path)) return path.slice(1, -1);
  if (/^-?\d+(\.\d+)?$/.test(path)) return Number(path);
  if (path === 'true') return true;
  if (path === 'false') return false;
  const parts = path.split('.');
  const head = parts[0];
  for (let i = scopes.length - 1; i >= 0; i--) {
    const scope = scopes[i];
    if (scope == null) continue;
    if (head === 'this' || head.startsWith('@')) {
      if (head in scope) return walk(scope[head], parts.slice(1));
      continue;
    }
    if (typeof scope === 'object' && head in scope) return walk(scope[head], parts.slice(1));
    // allow implicit this.x in each frames
    if (typeof scope === 'object' && 'this' in scope && scope.this && typeof scope.this === 'object' && head in scope.this) {
      return walk(scope.this[head], parts.slice(1));
    }
  }
  return undefined;
}

function walk(val, parts) {
  for (const p of parts) {
    if (val == null) return undefined;
    val = val[p];
  }
  return val;
}

function truthy(v) {
  if (Array.isArray(v)) return v.length > 0;
  return !!v;
}

function stringify(v) {
  if (v == null || v === false) return '';
  return String(v);
}

export function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
