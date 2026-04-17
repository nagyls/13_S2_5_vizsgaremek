import { test, expect } from '@playwright/test';

test('logout successfully', async ({ page }) => {

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
  const verificationBtn = page.locator('.verification-button');
  await verificationBtn.waitFor({ state: 'visible', timeout: 15000 });
  await verificationBtn.click();

  await page.waitForURL(/login/, { timeout: 15000 });

  // Belépés a logout teszteléséhez
  await page.locator('#login_email').fill(email);
  await page.locator('#login_password').fill(password);
  await page.click('#login_btn');

  // URL minta megváltoztatása dashboard-ról a specifikusabb redirektre
  await expect(page).toHaveURL(/dashboard|user-dashboard|pending-approval/, { timeout: 15000 });

  await page.click('.user-avatar');
  await expect(page.locator('.logout-btn')).toBeVisible();

  await page.click('.logout-btn');

  await expect(page).toHaveURL(/\/$/);

  const storage = await page.evaluate(() => {
    return localStorage.getItem('esemenyter_user');
  });

  expect(storage).toBeNull();
});