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

  await page.waitForURL(/dashboard/, { timeout: 6000 });

  await page.click('.user-avatar');
  await expect(page.locator('.logout-btn')).toBeVisible();

  await page.click('.logout-btn');

  await expect(page).toHaveURL(/\/$/);

  const storage = await page.evaluate(() => {
    return localStorage.getItem('esemenyter_user');
  });

  expect(storage).toBeNull();
});