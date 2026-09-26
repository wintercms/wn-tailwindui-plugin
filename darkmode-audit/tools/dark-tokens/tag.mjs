// Tag every rule inside darkmode.css's `.dark { … }` block with a `--dm-id` custom
// property so compiled CSSOM rules can be mapped back to their source rule.
//
//   node tag.mjs tag   <darkmode.css> <rules.json>   (rewrites darkmode.css in place)
//   node tag.mjs untag <darkmode.css>                (strips the tags again)
import fs from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire((process.env.WINTER_ROOT || process.cwd()) + '/package.json');
const postcss = require('postcss');

const [mode, file, out] = process.argv.slice(2);
const src = fs.readFileSync(file, 'utf8');
const root = postcss.parse(src);

if (mode === 'untag') {
    root.walkDecls('--dm-id', (d) => d.remove());
    fs.writeFileSync(file, root.toString());
    process.exit(0);
}

const darkBlock = root.nodes.find((n) => n.type === 'rule' && n.selector.trim() === '.dark');
if (!darkBlock) throw new Error('no top-level .dark block');

const rules = {};
let id = 0;
darkBlock.walkRules((rule) => {
    const own = rule.nodes.filter((n) => n.type === 'decl');
    if (!own.length) return;
    id++;
    // Full selector chain, outermost first, for the report only.
    const chain = [];
    for (let p = rule; p && p !== root; p = p.parent) {
        if (p.type === 'rule') chain.unshift(p.selector.replace(/\s+/g, ' ').trim());
        if (p.type === 'atrule') chain.unshift('@' + p.name + ' ' + p.params);
    }
    rules[id] = {
        line: rule.source.start.line,
        chain,
        decls: own.map((d) => ({ prop: d.prop, value: d.value, important: !!d.important, line: d.source.start.line })),
    };
    rule.prepend({ prop: '--dm-id', value: String(id) });
});

fs.writeFileSync(file, root.toString());
fs.writeFileSync(out, JSON.stringify(rules, null, 1));
console.log(`tagged ${id} rules`);
