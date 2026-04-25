import { chromium } from 'playwright';

(async () => {
    let browser;
    try {
        browser = await chromium.launch({ headless: true });
        const page = await browser.newPage();

        const query = encodeURIComponent('kurir OR ekspedisi OR paket OR pengiriman');
        const url = `https://news.google.com/search?q=${query}&hl=id&gl=ID&ceid=ID%3Aid`;

        await page.goto(url, { waitUntil: 'networkidle' });

        const articles = await page.$$eval('a.JtKRv', nodes => {
            return nodes.map(node => {
                const articleContainer = node.closest('c-wiz') || node.parentElement.parentElement;

                let timeEl = articleContainer ? articleContainer.querySelector('time') : null;
                // Try a few possible selectors for source, standard on Google news currently
                let sourceEl = articleContainer ? (articleContainer.querySelector('div[data-n-tid="9"]') || articleContainer.querySelector('.vr1PYe')) : null;

                return {
                    title: node.textContent,
                    url: node.href,
                    source: sourceEl ? sourceEl.textContent : 'Unknown Source',
                    published_at: timeEl ? timeEl.getAttribute('datetime') : null
                };
            }).filter(item => item.title && item.url);
        });

        console.log(JSON.stringify(articles, null, 2));
    } catch (err) {
        console.error(JSON.stringify({ error: err.message }));
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();