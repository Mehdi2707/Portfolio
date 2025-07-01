const { chromium } = require('playwright');
const url = process.argv[2];

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  await page.goto(url, { waitUntil: 'domcontentloaded' });

  // Tenter de fermer le popup cookie (si présent)
  try {
    await page.waitForSelector('#onetrust-accept-btn-handler', { timeout: 5000 });
    await page.click('#onetrust-accept-btn-handler');
  } catch {
    console.log('Pas de popup cookie');
  }

  // Maintenant cliquer sur ton bouton
  await page.waitForSelector('button.event-choice-map-fast-btn', { timeout: 7000 });
  await page.click('button.event-choice-map-fast-btn');

  // Attendre la liste des prix
  await page.waitForSelector('ul.session-price-list', { timeout: 7000 });
  const priceListHtml = await page.$eval('ul.session-price-list', el => el.outerHTML);

  console.log(priceListHtml);

  await browser.close();
})();
