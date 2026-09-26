// In-page harness for turning darkmode.css into a --wn-* token redefinition.
// Expects window.DM_RULES (rules.json from tag.mjs) and window.DM_TOKENS (token names).
(function () {
    const CAPTURED = [
        'color', 'background-color', 'background-image',
        'border-top-color', 'border-right-color', 'border-bottom-color', 'border-left-color',
        'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width',
        'border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style',
        'box-shadow', 'outline-color', 'outline-style', 'outline-width',
        'fill', 'stroke', 'text-shadow', 'text-decoration-color', 'opacity',
    ];
    const PSEUDOS = ['::before', '::after', '::placeholder'];
    const SIDES = ['top', 'right', 'bottom', 'left'];

    // Does a source declaration of `src` affect captured longhand `p`?
    function affects(src, p) {
        if (src === p) return true;
        if (src.startsWith('--')) return false;
        if (src === 'background') return p.startsWith('background-');
        if (src === 'outline') return p.startsWith('outline-');
        if (src === 'border') return p.startsWith('border-');
        const m = p.match(/^border-(top|right|bottom|left)-(color|width|style)$/);
        if (m) {
            const [, side, kind] = m;
            const axis = (side === 'top' || side === 'bottom') ? 'block' : 'inline';
            return src === 'border-' + kind || src === 'border-' + side || src === 'border-' + axis ||
                src === 'border-' + axis + '-' + kind;
        }
        return false;
    }
    const isCandidateProp = (src) => CAPTURED.some((p) => affects(src, p));

    function noTransitions() {
        let s = document.getElementById('dm-notrans');
        if (!s) {
            s = document.createElement('style');
            s.id = 'dm-notrans';
            s.textContent = '*,*::before,*::after{transition:none!important;animation:none!important;caret-color:transparent!important}';
            document.head.appendChild(s);
        }
    }

    function taggedRules() {
        const out = [];
        const walk = (list) => {
            for (const r of list) {
                if (r.cssRules && !(r instanceof CSSStyleRule)) walk(r.cssRules);
                if (r instanceof CSSStyleRule) {
                    const id = r.style.getPropertyValue('--dm-id').trim();
                    if (id) out.push({ id, rule: r });
                    if (r.cssRules && r.cssRules.length) walk(r.cssRules);
                }
            }
        };
        for (const sh of document.styleSheets) {
            let rules;
            try { rules = sh.cssRules; } catch (e) { continue; }
            walk(rules);
        }
        return out;
    }

    // Split a selector list on top-level commas.
    function splitList(sel) {
        const parts = []; let depth = 0, cur = '';
        for (const ch of sel) {
            if (ch === '(' || ch === '[') depth++;
            if (ch === ')' || ch === ']') depth--;
            if (ch === ',' && depth === 0) { parts.push(cur.trim()); cur = ''; } else cur += ch;
        }
        if (cur.trim()) parts.push(cur.trim());
        return parts;
    }
    // [{base, pseudo}] where pseudo is '' for the element itself.
    function selectorTargets(sel) {
        return splitList(sel).map((part) => {
            const m = part.match(/::?(before|after|placeholder|-webkit-input-placeholder|-moz-placeholder|selection|marker)\s*$/i);
            if (!m) return { base: part, pseudo: '' };
            let pseudo = '::' + m[1].toLowerCase();
            if (/placeholder/.test(pseudo)) pseudo = '::placeholder';
            return { base: part.slice(0, m.index) || '*', pseudo };
        });
    }

    function candidates() {
        const cands = [];
        for (const { id, rule } of taggedRules()) {
            const src = window.DM_RULES[id];
            if (!src) continue;
            const targets = selectorTargets(rule.selectorText);
            rule.__dmCands = [];
            for (const d of src.decls) {
                if (!isCandidateProp(d.prop)) continue;
                const c = { key: id + '|' + d.prop, id, prop: d.prop, value: d.value, important: d.important, rule, targets, removed: false };
                rule.__dmCands.push(c);
                cands.push(c);
            }
        }
        return cands;
    }
    // Shorthands in one rule can share longhands (border-top + border-color both set
    // border-top-color), so removing or restoring one declaration in isolation corrupts
    // its neighbours. After every change, re-apply the rule's still-active candidate
    // declarations in source order, which reproduces the cascade within the rule.
    // When parsed, an !important declaration is not overridden by a later normal one in
    // the same block, but CSSOM setProperty() just overwrites, so apply normal ones first.
    function reapply(rule) {
        for (const imp of [false, true]) {
            for (const c of rule.__dmCands) if (!c.removed && c.important === imp) rule.style.setProperty(c.prop, c.value, imp ? 'important' : '');
        }
    }
    function remove(c) { if (!c.removed) { c.rule.style.removeProperty(c.prop); c.removed = true; reapply(c.rule); } }
    function restore(c) { if (c.removed) { c.removed = false; reapply(c.rule); } }

    function pathOf(el) {
        const parts = [];
        for (let e = el; e && e.nodeType === 1; e = e.parentElement) {
            let i = 1; for (let s = e.previousElementSibling; s; s = s.previousElementSibling) i++;
            parts.unshift(e.tagName.toLowerCase() + ':' + i);
        }
        return parts.join('/');
    }

    function norm(cs, p, el) {
        const v = cs.getPropertyValue(p);
        let m = p.match(/^border-(top|right|bottom|left)-color$/);
        if (m && (cs.getPropertyValue('border-' + m[1] + '-style') === 'none' || parseFloat(cs.getPropertyValue('border-' + m[1] + '-width')) === 0)) return '-';
        if (p === 'outline-color' && (cs.outlineStyle === 'none' || parseFloat(cs.outlineWidth) === 0)) return '-';
        if (p === 'text-decoration-color' && cs.textDecorationLine === 'none') return '-';
        if ((p === 'fill' || p === 'stroke') && !(el instanceof SVGElement)) return '-';
        return v;
    }

    function capture() {
        const snap = new Map();
        const els = [document.documentElement, ...document.querySelectorAll('body, body *')];
        for (const el of els) {
            if (el.id === 'dm-notrans' || el.id === 'dm-tokens') continue;
            const key = pathOf(el);
            const cs = getComputedStyle(el);
            const rec = {};
            for (const p of CAPTURED) rec[p] = norm(cs, p, el);
            snap.set(key, { el, pseudo: '', rec });
            for (const ps of PSEUDOS) {
                const pcs = getComputedStyle(el, ps);
                if (ps !== '::placeholder' && (pcs.content === 'none' || pcs.content === 'normal')) continue;
                if (ps === '::placeholder' && !('placeholder' in el && el.placeholder)) continue;
                const prec = {};
                for (const p of CAPTURED) prec[p] = norm(pcs, p, el);
                snap.set(key + ps, { el, pseudo: ps, rec: prec });
            }
        }
        return snap;
    }

    function diff(a, b) {
        const out = [];
        for (const [k, va] of a) {
            const vb = b.get(k);
            if (!vb) continue;
            for (const p of CAPTURED) if (va.rec[p] !== vb.rec[p]) out.push({ k, p, was: va.rec[p], now: vb.rec[p], el: va.el, pseudo: va.pseudo });
        }
        return out;
    }

    function safeMatches(el, sel) { try { return el.matches(sel); } catch (e) { return false; } }
    function candMatches(c, el, pseudo) { return c.targets.some((t) => t.pseudo === pseudo && safeMatches(el, t.base)); }
    function candMatchedAny(c, snap) {
        for (const t of c.targets) {
            let hits; try { hits = document.querySelectorAll(t.base); } catch (e) { continue; }
            for (const el of hits) {
                if (!t.pseudo) return true;
                if (snap.has(pathOf(el) + t.pseudo)) return true;
            }
        }
        return false;
    }

    function setTokens(css) {
        let s = document.getElementById('dm-tokens');
        if (!s) { s = document.createElement('style'); s.id = 'dm-tokens'; document.head.appendChild(s); }
        s.textContent = css;
    }

    function describe(el, pseudo) {
        let d = el.tagName.toLowerCase();
        if (el.id) d += '#' + el.id;
        if (el.classList.length) d += '.' + [...el.classList].slice(0, 4).join('.');
        const parent = el.parentElement;
        let pd = '';
        if (parent) { pd = parent.tagName.toLowerCase(); if (parent.classList.length) pd += '.' + [...parent.classList].slice(0, 3).join('.'); }
        return (pd ? pd + ' > ' : '') + d + pseudo;
    }

    // Phase 1: which token paints which element-property, and what darkmode.css paints it today.
    function probe() {
        noTransitions();
        const base = capture();
        const cands = candidates();
        cands.forEach(remove);
        const tokens = window.DM_TOKENS;
        setTokens(':root, :root.dark, .dark { ' + tokens.map((t, i) => `${t}: rgb(${i + 1}, 2, 253);`).join(' ') + ' }');
        const probed = capture();
        const usage = {};   // token -> { baselineValue: {n, lightValue:{}, examples:[]} }
        const composite = {};
        for (const [k, pv] of probed) {
            const bv = base.get(k); if (!bv) continue;
            for (const p of CAPTURED) {
                const v = pv.rec[p];
                const m = v && v.match(/rgba?\((\d+), 2, 253(, [\d.]+)?\)/);
                if (!m) continue;
                const tok = tokens[+m[1] - 1];
                if (!tok) continue;
                const exact = /^rgba?\(\d+, 2, 253(, [\d.]+)?\)$/.test(v);
                const bucket = exact ? usage : composite;
                const b = (bucket[tok] = bucket[tok] || {});
                const key = p + ' => ' + bv.rec[p];
                const e = (b[key] = b[key] || { n: 0, examples: [] });
                e.n++;
                if (e.examples.length < 3) e.examples.push(describe(pv.el, pv.pseudo));
            }
        }
        // restore everything so the page is left as it was
        setTokens('');
        cands.forEach(restore);
        return { usage, composite };
    }

    // Phase 2: with the token block applied, which darkmode.css declarations are still needed here?
    function prune(tokenCss) {
        noTransitions();
        const base = capture();
        const cands = candidates();
        const matched = cands.filter((c) => candMatchedAny(c, base)).map((c) => c.key);
        setTokens(tokenCss);
        cands.forEach(remove);
        let rounds = 0, unresolved = [];
        for (;;) {
            rounds++;
            const d = diff(base, capture());
            if (!d.length) { unresolved = []; break; }
            let restored = 0;
            unresolved = [];
            for (const x of d) {
                const hit = cands.filter((c) => c.removed && affects(c.prop, x.p) && candMatches(c, x.el, x.pseudo));
                if (!hit.length) { unresolved.push(x); continue; }
                hit.forEach((c) => { restore(c); restored++; });
            }
            if (!restored || rounds > 40) break;
        }
        // Second pass: the restore step above restores every declaration that COULD
        // explain a difference. Re-test each one alone and leave it removed if doing so
        // introduces no difference beyond the ones the token block already makes.
        const sig = (x) => x.k + '|' + x.p + '|' + x.now;
        const allowed = new Set(diff(base, capture()).map(sig));
        for (const c of cands.filter((c) => !c.removed && candMatchedAny(c, base))) {
            remove(c);
            if (!diff(base, capture()).every((x) => allowed.has(sig(x)))) restore(c);
        }
        const needed = cands.filter((c) => !c.removed).map((c) => c.key);
        const unres = unresolved.map((x) => ({ at: describe(x.el, x.pseudo), p: x.p, was: x.was, now: x.now }));
        // leave the page in its original state
        setTokens('');
        cands.forEach(restore);
        return { rounds, candidates: cands.length, matched, needed, unresolved: unres };
    }

    // Resolve --drk-* variables to computed colours for reverse lookup.
    function drkValues() {
        const out = {};
        const probeEl = document.createElement('div');
        document.body.appendChild(probeEl);
        const names = new Set();
        for (const sh of document.styleSheets) {
            let rules; try { rules = sh.cssRules; } catch (e) { continue; }
            for (const r of rules) if (r.style) for (const p of r.style) if (p.startsWith('--drk-')) names.add(p);
        }
        for (const n of names) {
            probeEl.style.color = `var(${n})`;
            out[n] = getComputedStyle(probeEl).color;
        }
        probeEl.remove();
        return out;
    }

    window.DM = { probe, prune, capture, diff, candidates, drkValues, noTransitions, setTokens };
})();
