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

def test_add_feedback_category(driver):
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

        # === 2. MENUJU HALAMAN KATEGORI FEEDBACK ===
        link_category = driver.find_element(By.XPATH, '//a[contains(@href, "feedback")]')
        link_category.click()
        time.sleep(3)

        link_category = driver.find_element(By.XPATH, '//a[contains(@href, "feedback-category")]')
        link_category.click()
        time.sleep(3)

        # === 3. KLIK TOMBOL TAMBAH KATEGORI (BUKA MODAL) ===
        btn_tambah = driver.find_element(By.XPATH, '//button[@data-target="#createModal"]')
        btn_tambah.click()
        time.sleep(2) # Tunggu modal terbuka

        # === 4. ISI FORM DI DALAM MODAL ===
        nama_kategori_input = driver.find_element(By.NAME, "fbk_category_name")
        nama_kategori_baru = "Penambahan Fitur"
        nama_kategori_input.send_keys(nama_kategori_baru)
        time.sleep(2)

        # === 5. KLIK TOMBOL SIMPAN ===
        btn_simpan = driver.find_element(By.XPATH, '//div[@id="createModal"]//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4) # Tunggu proses simpan dan DataTables memuat ulang

        # === 6. VALIDASI ===
        assert nama_kategori_baru in driver.page_source
        print("Pengujian tambah data Kategori Feedback sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")