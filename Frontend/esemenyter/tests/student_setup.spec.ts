import { test, expect } from '@playwright/test';

test('student setup successfully', async ({ page }) => {
  const email = `diak${Date.now()}@gmail.com`;
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

  // Belépés
  await page.locator('#login_email').fill(email);
  await page.locator('#login_password').fill(password);
  await page.click('#login_btn');

  // URL minta megváltoztatása dashboard-ról a specifikusabb redirektre
  await expect(page).toHaveURL(/dashboard|user-dashboard|pending-approval/, { timeout: 15000 });

  await page.locator('.role-card.student').click();
  await expect(page.locator('.role-card.student')).toHaveClass(/selected/);

  const studentWizard = page.locator('.setup-wizard');
  try {
    await studentWizard.waitFor({ state: 'visible', timeout: 4000 });
  } catch {
    await page.locator('.role-card.student .card-action-btn').click({ force: true });
    await studentWizard.waitFor({ state: 'visible', timeout: 10000 });
  }

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