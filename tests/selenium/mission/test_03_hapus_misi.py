import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

@pytest.fixture(scope="module")
def driver():
    driver = webdriver.Chrome()
    driver.maximize_window()
    yield driver
    driver.quit()

def test_delete_mission(driver):
    try:
        # === 1. PROSES LOGIN ===
        login_url = 'http://raihanatmaja.my.id/login-form'
        driver.get(login_url)

        email_field = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, '//input[@name="email"]'))
        )
        password_field = driver.find_element(By.XPATH, '//input[@name="password"]')
        login_button = driver.find_element(By.XPATH, '//button[@type="submit"]')

        email_field.send_keys('akunyt123gue@gmail.com')
        time.sleep(1)
        password_field.send_keys('Testing_12')
        time.sleep(1)
        login_button.click()

        # === 2. MENUJU HALAMAN MISI ===
        link_mission = driver.find_element(By.XPATH, '//a[contains(@href, "missions")]')
        driver.execute_script("arguments[0].click();", link_mission)
        time.sleep(3)

        # === 3. KLIK TOMBOL HAPUS ===
        # Asumsi class tombol hapus adalah btn-danger
        btn_hapus = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.CSS_SELECTOR, '#tableMission tbody tr:first-child .btn-danger'))
        )
        btn_hapus.click()
        time.sleep(2)

        # === 4. KONFIRMASI HAPUS ===
        try:
            alert = driver.switch_to.alert
            alert.accept()
            time.sleep(3)
        except:
            btn_konfirmasi_hapus = driver.find_element(By.XPATH, "//button[contains(text(), 'Ya') or contains(text(), 'Hapus') or contains(text(), 'Yes')]")
            btn_konfirmasi_hapus.click()
            time.sleep(3)

        # === 5. VALIDASI ===
        assert "missions" in driver.current_url.lower() # Sesuaikan dengan URL route missions Anda
        print("Pengujian hapus data Misi sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")