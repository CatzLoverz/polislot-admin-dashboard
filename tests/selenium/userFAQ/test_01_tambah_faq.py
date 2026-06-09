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

def test_add_faq(driver):
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

        # === 2. MENUJU HALAMAN FAQ ===
        # Menggunakan JS Executor untuk menghindari elemen tertutup
        link_faq = driver.find_element(By.XPATH, '//a[contains(@href, "user-faq")]')
        link_faq.click()
        time.sleep(3)

        # === 3. KLIK TOMBOL TAMBAH FAQ (BUKA MODAL) ===
        btn_tambah = driver.find_element(By.ID, "btn-tambah-faq")
        btn_tambah.click()
        time.sleep(2) # Tunggu modal terbuka dan animasi selesai

        # === 4. ISI FORM DI DALAM MODAL ===
        pertanyaan_input = driver.find_element(By.ID, "faq_question")
        pertanyaan_baru = "Apakah aplikasi PoliSlot gratis?"
        pertanyaan_input.send_keys(pertanyaan_baru)
        time.sleep(2)

        jawaban_input = driver.find_element(By.ID, "faq_answer")
        jawaban_input.send_keys("Ya, aplikasi PoliSlot sepenuhnya gratis digunakan oleh civitas akademika.")
        time.sleep(2)

        # === 5. KLIK TOMBOL SIMPAN ===
        btn_simpan = driver.find_element(By.ID, "btn-submit-faq")
        btn_simpan.click()
        time.sleep(4) # Tunggu proses simpan dan DataTables memuat ulang / SweetAlert muncul

        # === 6. VALIDASI ===
        assert pertanyaan_baru in driver.page_source
        print("Pengujian tambah data FAQ sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")