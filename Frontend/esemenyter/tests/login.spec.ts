import { test, expect } from '@playwright/test';

test('successful login', async ({ page }) => {

  const email = `teszt${Date.now()}@gmail.com`;
  const password = 'Teszt123';

  await page.goto('http://localhost:5173/register');

  await page.fill('#username', 'Teszt Elek');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.fill('#password_confirmation', password);

  await page.check('#accept_terms');
  await page.click('#register_btn');

  await expect(page).toHaveURL(/dashboard/);

  await page.evaluate(() => {
    localStorage.clear();
    sessionStorage.clear();
  });

  await page.goto('http://localhost:5173/login');
  await expect(page).toHaveURL(/login/);

  await page.fill('#email', email);
  await page.fill('#password', password);

  await page.click('#login_btn');

  await expect(page).toHaveURL(/dashboard/);
});