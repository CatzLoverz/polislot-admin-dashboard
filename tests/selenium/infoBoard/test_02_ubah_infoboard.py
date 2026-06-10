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

def test_edit_info_board(driver):
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

        # === 3. KLIK TOMBOL EDIT DI DALAM TABEL ===
        # Mencari tombol dengan class 'btn-edit' di baris pertama tabel
        btn_edit = driver.find_element(By.CSS_SELECTOR, '.btn-edit')
        btn_edit.click()
        time.sleep(2) # Tunggu modal Edit terbuka dan data terisi otomatis

        # === 4. UBAH ISI FORM DI DALAM MODAL ===
        judul_input = driver.find_element(By.ID, "edit_info_title")
        judul_input.clear() # Hapus teks lama
        time.sleep(1)
        judul_input.send_keys("Pengumuman Telah Diupdate oleh kaneki")
        time.sleep(2)

        konten_input = driver.find_element(By.ID, "edit_info_content")
        konten_input.clear() # Hapus teks lama
        time.sleep(1)
        konten_input.send_keys("siapa yang nyulik my jepit-jepit aku paranin!")
        time.sleep(2)

        # === 5. KLIK TOMBOL SIMPAN PERUBAHAN ===
        # Mencari tombol submit di dalam #editModal
        btn_simpan = driver.find_element(By.XPATH, '//div[@id="editModal"]//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4) # Tunggu proses simpan dan tabel dimuat ulang

        # === 6. VALIDASI ===
        assert "Pengumuman Telah Diupdate" in driver.page_source
        print("Pengujian edit (ubah) data Info Board sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")