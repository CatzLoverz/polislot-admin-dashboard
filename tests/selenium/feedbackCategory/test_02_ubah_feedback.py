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

def test_edit_feedback_category(driver):
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
        link_category = driver.find_element(By.XPATH, '//a[contains(@href, "feedback-category")]')
        driver.execute_script("arguments[0].click();", link_category)
        time.sleep(3)

        # === 3. KLIK TOMBOL EDIT DI DALAM TABEL ===
        # Asumsi: script Anda menggunakan class .btn-edit di setiap baris aksi
        btn_edit = driver.find_element(By.CSS_SELECTOR, '#category-table tbody tr:first-child .btn-edit')
        btn_edit.click()
        time.sleep(2)

        # === 4. UBAH DATA DI DALAM MODAL ===
        nama_kategori_input = driver.find_element(By.ID, "edit_name")
        nama_kategori_input.clear() # Hapus teks yang lama
        time.sleep(1)
        
        nama_kategori_update = "Bug pada fitur"
        nama_kategori_input.send_keys(nama_kategori_update)
        time.sleep(2)

        # === 5. KLIK TOMBOL SIMPAN PERUBAHAN ===
        btn_simpan = driver.find_element(By.XPATH, '//div[@id="editModal"]//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4)

        # === 6. VALIDASI ===
        assert nama_kategori_update in driver.page_source
        print("Pengujian ubah data Kategori Feedback sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")