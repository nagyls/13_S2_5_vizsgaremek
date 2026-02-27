import { test, expect } from '@playwright/test';

test('registration passwords do not match fails', async ({ page }) => {

  await page.goto('http://localhost:5173/register');

  await page.fill('#username', 'Teszt Elek');
  await page.fill('#email', `teszt${Date.now()}@gmail.com`);

  await page.fill('#password', 'Teszt123');
  await page.fill('#password_confirmation', 'Masik123');

  await page.locator('.checkbox-container').click();
  await page.click('#register_btn');

  await expect(page).toHaveURL(/dashboard/);

});