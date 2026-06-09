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

def test_edit_faq(driver):
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
        link_faq = driver.find_element(By.XPATH, '//a[contains(@href, "user-faq")]')
        link_faq.click()
        time.sleep(3)

        # === 3. KLIK TOMBOL EDIT DI DALAM TABEL ===
        # Mencari tombol edit di baris paling atas tabel
        btn_edit = driver.find_element(By.CSS_SELECTOR, '#faq-table tbody tr:first-child .btn-edit')
        btn_edit.click()
        time.sleep(2) # Tunggu modal terbuka dan data lama terisi oleh script JS Anda

        # === 4. UBAH DATA DI DALAM MODAL ===
        pertanyaan_input = driver.find_element(By.ID, "faq_question")
        pertanyaan_input.clear() # Hapus teks pertanyaan lama
        time.sleep(1)
        pertanyaan_update = "Bagaimana jika lupa password PoliSlot?"
        pertanyaan_input.send_keys(pertanyaan_update)
        time.sleep(2)

        jawaban_input = driver.find_element(By.ID, "faq_answer")
        jawaban_input.clear() # Hapus teks jawaban lama
        time.sleep(1)
        jawaban_input.send_keys("Anda dapat menggunakan fitur Lupa Password di halaman login awal.")
        time.sleep(2)

        # === 5. KLIK TOMBOL PERBARUI ===
        btn_simpan = driver.find_element(By.ID, "btn-submit-faq")
        btn_simpan.click()
        time.sleep(4)

        # === 6. VALIDASI ===
        assert pertanyaan_update in driver.page_source
        print("Pengujian ubah data FAQ sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")