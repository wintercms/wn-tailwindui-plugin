// Apply removable.json (keys "ruleId|prop") to an untagged darkmode.css, using the
// same rule numbering as tag.mjs, and drop rules left empty. Comments are left for
// manual review, since one above an emptied rule may head a whole section.
//
//   node apply.mjs <darkmode.orig.css> <removable.json> <out.css>
import fs from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire((process.env.WINTER_ROOT || process.cwd()) + '/package.json');
const postcss = require('postcss');

const [src, removableFile, out] = process.argv.slice(2);
const removable = new Set(JSON.parse(fs.readFileSync(removableFile, 'utf8')));
const root = postcss.parse(fs.readFileSync(src, 'utf8'));
const darkBlock = root.nodes.find((n) => n.type === 'rule' && n.selector.trim() === '.dark');

let id = 0, removedDecls = 0, removedRules = 0;
const touched = [];
darkBlock.walkRules((rule) => {
    const own = rule.nodes.filter((n) => n.type === 'decl');
    if (!own.length) return;
    id++;
    for (const d of own) {
        if (removable.has(id + '|' + d.prop)) { d.remove(); removedDecls++; }
    }
    touched.push(rule);
});

// Remove rules with nothing left (walk deepest-first so parents empty out too).
const isEmpty = (n) => !n.nodes || n.nodes.every((c) => c.type === 'comment');
for (const rule of touched.reverse()) {
    for (let p = rule; p && p !== darkBlock && p.type === 'rule' && isEmpty(p);) {
        const parent = p.parent;
        p.remove(); removedRules++;
        p = parent;
    }
}

fs.writeFileSync(out, root.toString());
console.log(`removed ${removedDecls} declarations, ${removedRules} emptied rules`);
