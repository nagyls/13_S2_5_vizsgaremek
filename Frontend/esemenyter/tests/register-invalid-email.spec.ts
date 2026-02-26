import { test, expect } from '@playwright/test';

test('registration fail', async ({ page }) => {
  await page.goto('http://localhost:5173/register');

  await page.fill('#username', 'Teszt Elek');
  await page.fill('#email', `teszt${Date.now()}@gmail.com`);
  await page.fill('#password', 'Teszt123');
  await page.fill('#password_confirmation', 'Teszt123');

  await page.click('#register_btn');

  await expect(page).toHaveURL(/register/);
  await expect(page.locator('.error-message')).toBeVisible();
});