const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', error => console.log('PAGE ERROR:', error.message));
    
    await page.goto('http://localhost:9000/assistant', { waitUntil: 'networkidle2' });
    
    // Check if tailwind applied
    const isStyled = await page.evaluate(() => {
        return !!document.getElementById('tailwind-style');
    });
    console.log('Is styled:', isStyled);
    
    await browser.close();
})();
