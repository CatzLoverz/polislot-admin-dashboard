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

def test_delete_info_board(driver):
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

        # === 2. MENUJU HALAMAN INFO BOARD ===
        link_infoboard = driver.find_element(By.XPATH, '//a[contains(@href, "info-board")]')
        link_infoboard.click()
        time.sleep(3)

        # === 3. KLIK TOMBOL HAPUS DI DALAM TABEL ===
        # Asumsi: Tombol hapus memiliki class 'btn-danger' atau terdapat icon 'fa-trash'
        # Jika class tombol hapus Anda berbeda (misal: 'btn-delete'), ubah teks di bawah ini.
        btn_hapus = driver.find_element(By.CSS_SELECTOR, '#infoboard-table tbody tr:first-child .btn-danger')
        btn_hapus.click()
        time.sleep(2)

        # === 4. KONFIRMASI HAPUS (ALERTS / SWEETALERT) ===
        try:
            # Jika menggunakan Pop-up Alert bawaan browser (Javascript Alert)
            alert = driver.switch_to.alert
            alert.accept() # Menekan tombol "OK/Yes"
            time.sleep(3)
        except:
            # Jika menggunakan SweetAlert atau Modal Custom (Cari tombol Konfirmasi)
            # Asumsi teks tombol adalah "Ya, Hapus!" atau "Yes"
            btn_konfirmasi_hapus = driver.find_element(By.XPATH, "//button[contains(text(), 'Ya') or contains(text(), 'Hapus') or contains(text(), 'Yes')]")
            btn_konfirmasi_hapus.click()
            time.sleep(3)

        # === 5. VALIDASI ===
        assert "info-board" in driver.current_url.lower()
        print("Pengujian hapus data Info Board sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")