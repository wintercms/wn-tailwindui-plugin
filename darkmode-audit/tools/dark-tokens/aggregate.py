"""Choose a dark value for each --wn-* token from the probe results.

usage: python3 aggregate.py probe-dump.json drk.json tokens.less > report.txt
writes tokens.js (DM_TOKEN_CSS) and token-choices.json next to this script.
"""
import json, re, sys, os
from collections import defaultdict

dump = json.load(open(sys.argv[1]))
drk = json.load(open(sys.argv[2]))
tokens_less = open(sys.argv[3]).read()
here = os.path.dirname(os.path.abspath(__file__))


def hex_to_rgb(h):
    h = h.lstrip('#')
    if len(h) == 3:
        h = ''.join(c * 2 for c in h)
    r, g, b = int(h[0:2], 16), int(h[2:4], 16), int(h[4:6], 16)
    return f'rgb({r}, {g}, {b})'


light = {}
for m in re.finditer(r'^\s*(--wn-[a-z0-9-]+):\s*(#[0-9a-fA-F]{3,6})\s*;', tokens_less, re.M):
    light.setdefault(m.group(1), hex_to_rgb(m.group(2)))

# token -> value -> {n, props, examples, pages}
agg = defaultdict(lambda: defaultdict(lambda: {'n': 0, 'props': set(), 'examples': [], 'pages': set()}))
for page, res in dump.items():
    for tok, buckets in res['usage'].items():
        for key, e in buckets.items():
            prop, val = key.split(' => ', 1)
            a = agg[tok][val]
            a['n'] += e['n']
            a['props'].add(prop)
            a['pages'].add(page)
            for ex in e['examples']:
                if len(a['examples']) < 4 and ex not in a['examples']:
                    a['examples'].append(ex)

# reverse lookup: computed colour -> --drk-* name (prefer the shortest / base names)
rev = {}
for name, val in sorted(drk.items(), key=lambda kv: (len(kv[0]), kv[0])):
    rev.setdefault(val, name)

choices = {}
lines = []
for tok in sorted(agg, key=lambda t: -sum(v['n'] for v in agg[t].values())):
    vals = agg[tok]
    total = sum(v['n'] for v in vals.values())
    best, info = max(vals.items(), key=lambda kv: kv[1]['n'])
    lt = light.get(tok)
    lines.append(f'\n{tok}  (light {lt}, {total} element-props)')
    for v, i in sorted(vals.items(), key=lambda kv: -kv[1]['n']):
        tag = ' = LIGHT' if v == lt else ''
        lines.append(f"   {i['n']:4d}  {v}{tag}  [{','.join(sorted(i['props']))}]  pages={len(i['pages'])}  e.g. {' | '.join(i['examples'][:2])}")
    if best == lt or best in ('-', '', 'none'):
        lines.append('   -> keep light (not redefined)')
        continue
    if not best.startswith('rgb'):
        lines.append('   -> skip (non-colour winner)')
        continue
    expr = f'var({rev[best]})' if best in rev else best
    choices[tok] = {'value': expr, 'computed': best, 'share': round(info['n'] / total, 2)}
    lines.append(f'   -> {expr}  ({choices[tok]["share"]:.0%} of uses)')

css = '.dark {\n' + '\n'.join(f'    {t}: {c["value"]};' for t, c in choices.items()) + '\n}\n'
open(os.path.join(here, 'tokens.js'), 'w').write('window.DM_TOKEN_CSS = ' + json.dumps(css) + ';\n')
json.dump(choices, open(os.path.join(here, 'token-choices.json'), 'w'), indent=1)
print(f'{len(choices)} tokens redefined for dark, {len(agg) - len(choices)} used but left light')
print('\n'.join(lines))
