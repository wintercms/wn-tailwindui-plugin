"""Combine per-page prune results.

usage: python3 analyse.py prune-dump.json rules.json
A declaration is removable when it matched something on at least one page and no
page needed it. Declarations that matched nothing anywhere are kept (unproven).
Writes removable.json; prints the changes the token block makes on its own.
"""
import json, sys
from collections import defaultdict

dump = json.load(open(sys.argv[1]))
rules = json.load(open(sys.argv[2]))

matched, needed = set(), set()
for page, r in dump.items():
    matched |= set(r['matched'])
    needed |= set(r['needed'])
removable = sorted(matched - needed, key=lambda k: (int(k.split('|')[0]), k))
json.dump(removable, open('removable.json', 'w'), indent=1)

total = sum(1 for r in rules.values() for d in r['decls'])
print(f'pages: {len(dump)}   declarations in .dark: {total}')
print(f'candidate declarations matched on some page: {len(matched)}')
print(f'still needed somewhere: {len(needed & matched)}   removable: {len(removable)}')

# group the token block's own visual changes
changes = defaultdict(lambda: {'pages': set(), 'n': 0})
for page, r in dump.items():
    for u in r['unresolved']:
        k = (u['p'], u['was'], u['now'], u['at'])
        changes[k]['pages'].add(page)
        changes[k]['n'] += 1
by_value = defaultdict(lambda: {'n': 0, 'at': set(), 'pages': set()})
for (p, was, now, at), v in changes.items():
    b = by_value[(p, was, now)]
    b['n'] += v['n']; b['at'].add(at); b['pages'] |= v['pages']
print(f'\nchanges the token block makes where darkmode.css had no override: {sum(v["n"] for v in by_value.values())}')
for (p, was, now), v in sorted(by_value.items(), key=lambda kv: -kv[1]['n']):
    print(f"{v['n']:5d}  {p}: {was} -> {now}   pages={','.join(sorted(v['pages']))[:80]}")
    for a in sorted(v['at'])[:3]:
        print(f'         {a[:150]}')
