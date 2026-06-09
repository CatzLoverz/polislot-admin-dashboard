import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
import time

@pytest.fixture(scope="module")
def driver():
    driver = webdriver.Chrome()
    driver.maximize_window()
    yield driver
    driver.quit()

def test_view_profile(driver):
    try:
        # === 1. PROSES LOGIN ===
        login_url = 'http://raihanatmaja.my.id/login-form'
        driver.get(login_url)

        email_field = driver.find_element(By.XPATH, '//input[@name="email"]')
        password_field = driver.find_element(By.XPATH, '//input[@name="password"]')
        login_button = driver.find_element(By.XPATH, '//button[@type="submit"]')

        email_field.send_keys('akunyt123gue@gmail.com')
        time.sleep(2)
        password_field.send_keys('Testing_12')
        time.sleep(2)
        login_button.click()
        time.sleep(2)

        # === 2. MENUJU HALAMAN PROFIL ===
        # Asumsi: link menuju profil di sidebar memiliki kata "profile" di URL-nya
        link_profile = driver.find_element(By.XPATH, '//a[contains(@href, "profile")]')
        driver.execute_script("arguments[0].click();", link_profile)
        time.sleep(3)

        # === 3. VALIDASI MELIHAT PROFIL ===
        # Memastikan URL berubah ke halaman profil
        assert "profile" in driver.current_url.lower()
        
        # Opsional: Memastikan judul card "Informasi Pribadi" tampil di layar
        assert "Informasi Pribadi" in driver.page_source
        
        print("Pengujian melihat halaman profil sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")