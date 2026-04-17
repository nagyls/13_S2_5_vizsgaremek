import { test, expect } from '@playwright/test';

test('successful registration', async ({ page }) => {

  const email = `teszt${Date.now()}@gmail.com`;
  const password = 'Teszt123';

  await page.goto('http://localhost:5173/register');

  await page.fill('#username', 'Teszt Elek');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.fill('#password_confirmation', password);

  await page.locator('.checkbox-container').click();

  await page.click('#register_btn');

  // Email verifikáció bypassolása a backend-en keresztül
  await page.request.post('http://localhost:8000/api/test/verify-email', {
    data: { email: email }
  });

  // A regisztráció után megjelenő popup-on kattintsunk a "Tovább a bejelentkezéshez" gombra
  await page.click('.verification-button');

  await page.waitForURL(/login/, { timeout: 15000 });

});