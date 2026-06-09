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

def test_edit_profile_name(driver):
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
        link_profile = driver.find_element(By.XPATH, '//a[contains(@href, "profile")]')
        driver.execute_script("arguments[0].click();", link_profile)
        time.sleep(3)

        # === 3. UBAH DATA NAMA ===
        # Mencari input nama berdasarkan ID
        nama_input = driver.find_element(By.ID, "name")

        # Menghapus nama lama
        nama_input.clear()
        time.sleep(1)

        # Memasukkan nama baru
        nama_baru = "Admin testing"
        nama_input.send_keys(nama_baru)
        time.sleep(2)

        # === 4. KLIK TOMBOL SIMPAN ===
        # Mencari tombol submit di dalam form
        btn_simpan = driver.find_element(By.XPATH, '//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4) # Tunggu proses simpan dan halaman dimuat ulang

        # === 5. VALIDASI HASIL UBAH ===
        # Memastikan nama yang baru berhasil tersimpan dan tampil di layar
        assert nama_baru in driver.page_source
        print("Pengujian ubah nama profil sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")