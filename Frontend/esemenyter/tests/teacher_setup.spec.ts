import { test, expect } from '@playwright/test';

test('teacher setup successfully', async ({ page }) => {
  const email = `tanar${Date.now()}@gmail.com`;
  const password = 'Teszt123';

  await page.goto('http://localhost:5173/register');


  await page.fill('#username', 'Teszt Elek');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.fill('#password_confirmation', password);

  await page.locator('.checkbox-container').click();

  await page.click('#register_btn');

  await page.waitForURL(/dashboard/, { timeout: 6000 });

  await page.click('.role-card.teacher');
  await page.click('.role-card.teacher .card-action-btn');
  await page.waitForSelector('.setup-wizard');

  await page.waitForSelector('.suggestions-grid .suggestion-card');
  await page.locator('.suggestion-card', { hasText: /Bács-Kiskun/i }).click();
  await page.click('button.btn-primary:has-text("Következő")');

  await page.waitForSelector('.suggestions-grid .suggestion-card');
  await page.locator('.suggestion-card', { hasText: /Kiskunfélegyháza/i }).first().click();
  await page.click('button.btn-primary:has-text("Következő")');

  await page.waitForSelector('.suggestions-grid .suggestion-card');
  await page.locator('.suggestion-card', { hasText: /Kiskunfélegyháza/i }).click();
  await page.click('button.btn-primary:has-text("Következő")');

  await page.waitForSelector('.suggestions-grid .suggestion-card');
  await page.locator('.suggestion-card', { hasText: /PÉGÉ/i }).click();
  await page.click('button.btn-primary:has-text("Következő")');

  await expect(page.locator('.confirmation-card')).toBeVisible();
  await page.click('button.btn-primary:has-text("Profil mentése")');

  await page.waitForURL(/pending-approval/, { timeout: 10000 });
});