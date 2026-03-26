import { test, expect } from '@playwright/test';

test('admin duplicate school fails', async ({ page }) => {

  const pause = async () => {
    if (process.env.SLOW_MODE) {
      await page.waitForTimeout(2000);
    }
  };

  const timestamp = Date.now();
  const email = `admin${timestamp}@gmail.com`;
  const password = 'Teszt123';
  const duplicateSchoolName = 'PÉGÉ';

  await page.goto('http://localhost:5173/register');


  await page.fill('#username', 'Teszt Elek');
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.fill('#password_confirmation', password);

  await page.locator('.checkbox-container').click();

  await page.click('#register_btn');

  await page.waitForURL(/dashboard/, { timeout: 15000 });

  await page.waitForFunction(() =>
    localStorage.getItem('esemenyter_token') !== null
  );

  await page.locator('.role-card.admin').click();
  await expect(page.locator('.role-card.admin')).toHaveClass(/selected/);
  await pause();

  const adminWizard = page.locator('.setup-wizard');
  try {
    await adminWizard.waitFor({ state: 'visible', timeout: 4000 });
  } catch {
    await page.locator('.role-card.admin .card-action-btn').click({ force: true });
    await adminWizard.waitFor({ state: 'visible', timeout: 10000 });
  }
  await pause();

  const nextButton = page.locator('.wizard-actions .btn-primary');

  const selectFirstCard = async () => {
    const card = page.locator('.suggestion-card').first();
    await expect(card).toBeVisible();
    await card.click();
    await expect(card).toHaveClass(/selected/);
    await pause();
  };

  await selectFirstCard();
  await nextButton.click();
  await pause();

  await selectFirstCard();
  await nextButton.click();
  await pause();

  await selectFirstCard();
  await nextButton.click();
  await pause();

  await expect(page.locator('#school_name')).toBeVisible();

  await page.fill('#school_name', duplicateSchoolName);
  await page.fill('#school_description', 'Ez egy teszt intézmény.');
  await page.fill('#school_address', 'Teszt utca 1.');
  await page.fill('#school_phone', '+36 1 234 5678');
  await page.fill('#school_email', `teszt${timestamp}@iskola.hu`);
  await page.fill('#school_website', 'https://www.tesztiskola.hu');

  await nextButton.click();
  await pause();

  await page.check('#admin_agreement');
  await pause();

  await nextButton.click();
  await pause();

  await expect(page).not.toHaveURL(/institution-dashboard/);
  await expect(page.locator('.toast.toast-error .toast-message').first()).toBeVisible({ timeout: 10000 });

});