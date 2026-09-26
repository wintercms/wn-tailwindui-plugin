async (page) => {
    // Phase is read from the page's localStorage key 'dm:phase' ('probe' | 'prune'),
    // set by the caller beforehand; results land in localStorage under 'dm:<phase>:<page>'.
    // Absolute path to this directory's working copy, readable by the Playwright MCP server.
    const D = '/ABSOLUTE/PATH/TO/dark-tokens/';
    const origin = 'https://winter.test';  // your local backend
    await page.goto(origin + '/backend', { waitUntil: 'networkidle' });
    const phase = await page.evaluate(() => localStorage.getItem('dm:phase'));
    await page.addScriptTag({ path: D + 'pages.js' });
    const pages = await page.evaluate(() => window.DM_PAGES);
    const log = [];
    for (const p of pages) {
        try {
            const resp = await page.goto(origin + p.url, { waitUntil: 'networkidle', timeout: 45000 });
            await page.waitForTimeout(600);
            for (const f of ['data.js', 'pages.js', 'harness.js']) await page.addScriptTag({ path: D + f });
            // tokens.js (window.DM_TOKEN_CSS) is optional: extra CSS to inject while pruning,
            // e.g. a token block being trialled before it is written into darkmode.css.
            if (phase === 'prune') await page.addScriptTag({ path: D + 'tokens.js' }).catch(() => {});
            if (p.prep) await page.evaluate((n) => window.DM_PREP[n](), p.prep);
            const summary = await page.evaluate(({ phase, name }) => {
                const res = phase === 'prune' ? DM.prune(window.DM_TOKEN_CSS || '') : DM.probe();
                localStorage.setItem('dm:' + phase + ':' + name, JSON.stringify(res));
                return phase === 'prune'
                    ? `matched ${res.matched.length} needed ${res.needed.length} unresolved ${res.unresolved.length} rounds ${res.rounds}`
                    : `tokens ${Object.keys(res.usage).length}`;
            }, { phase, name: p.name });
            log.push(`${p.name} [${resp && resp.status()}] ${summary}`);
        } catch (e) {
            log.push(`${p.name} ERROR ${String(e).slice(0, 160)}`);
        }
    }
    return log.join('\n');
}
