"""Write data.js for the harness: the tagged rule map plus core's token names.

usage (from the Winter root): python3 <this dir>/make-data.py <rules.json>
"""
import json, os, re, sys

here = os.path.dirname(os.path.abspath(__file__))
rules = json.load(open(sys.argv[1]))
tokens = []
for line in open('modules/system/assets/ui/less/tokens.less'):
    m = re.match(r'\s*(--wn-[a-z0-9-]+):', line)
    if m and m.group(1) not in tokens:
        tokens.append(m.group(1))
with open(os.path.join(here, 'data.js'), 'w') as f:
    f.write('window.DM_RULES = ' + json.dumps(rules) + ';\n')
    f.write('window.DM_TOKENS = ' + json.dumps(tokens) + ';\n')
print(f'{len(rules)} rules, {len(tokens)} tokens')
