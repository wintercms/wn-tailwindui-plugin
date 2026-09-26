async (page) => {
    // For each page: capture with the new build, swap the TailwindUI stylesheet back to
    // the original compiled CSS (_baseline.css) in place, capture again, and report the
    // differences grouped by property/old/new value. Results go to localStorage 'dm:verify:<page>'.
    // Absolute path to this directory's working copy, readable by the Playwright MCP server.
    const D = '/ABSOLUTE/PATH/TO/dark-tokens/';
    const origin = 'https://winter.test';  // your local backend
    await page.goto(origin + '/backend', { waitUntil: 'networkidle' });
    await page.addScriptTag({ path: D + 'pages.js' });
    const pages = await page.evaluate(() => window.DM_PAGES);
    const log = [];
    for (const p of pages) {
        try {
            await page.goto(origin + p.url, { waitUntil: 'networkidle', timeout: 45000 });
            await page.waitForTimeout(600);
            for (const f of ['data.js', 'pages.js', 'harness.js']) await page.addScriptTag({ path: D + f });
            if (p.prep) await page.evaluate((n) => window.DM_PREP[n](), p.prep);
            const res = await page.evaluate(async (name) => {
                DM.noTransitions();
                const now = DM.capture();
                const link = [...document.querySelectorAll('link[rel=stylesheet]')].find((l) => /tailwindui\/assets\/dist\/assets\/app-/.test(l.href));
                await new Promise((resolve) => {
                    const alt = document.createElement('link');
                    alt.rel = 'stylesheet';
                    alt.href = link.href.replace(/app-[^/]+\.css.*/, '_baseline.css?' + Date.now());
                    alt.onload = resolve;
                    link.after(alt);
                    link.disabled = true;
                    link.remove();
                });
                const was = DM.capture();
                const groups = {};
                for (const d of DM.diff(was, now)) {
                    const k = d.p + ': ' + d.was + ' -> ' + d.now;
                    groups[k] = (groups[k] || 0) + 1;
                }
                localStorage.setItem('dm:verify:' + name, JSON.stringify(groups));
                return Object.values(groups).reduce((a, b) => a + b, 0);
            }, p.name);
            log.push(`${p.name} diffs ${res}`);
        } catch (e) {
            log.push(`${p.name} ERROR ${String(e).slice(0, 160)}`);
        }
    }
    return log.join('\n');
}
