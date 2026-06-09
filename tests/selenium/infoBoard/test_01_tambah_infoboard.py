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

def test_add_info_board(driver):
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
        # Mencari link sidebar Info Board (Sesuaikan isi @href dengan route asli di sidebar Anda)
        link_infoboard = driver.find_element(By.XPATH, '//a[contains(@href, "info-board")]')
        link_infoboard.click()
        time.sleep(3)

        # === 3. KLIK TOMBOL TAMBAH PENGUMUMAN ===
        # Mencari tombol berdasarkan atribut data-target="#createModal"
        btn_tambah_modal = driver.find_element(By.XPATH, '//button[@data-target="#createModal"]')
        btn_tambah_modal.click()
        time.sleep(2) 

        # === 4. ISI FORM DI DALAM MODAL ===
        judul_input = driver.find_element(By.NAME, "info_title")
        judul_input.send_keys("Pengumuman Maintenance Server")
        time.sleep(2)

        konten_input = driver.find_element(By.NAME, "info_content")
        konten_input.send_keys("Diberitahukan kepada seluruh pengguna, sistem akan mengalami perbaikan pada malam ini pukul 00.00 WIB.")
        time.sleep(2)

        # === 5. KLIK TOMBOL SIMPAN ===
        # Mencari tombol submit spesifik di dalam #createModal agar tidak tertukar dengan modal Edit
        btn_simpan = driver.find_element(By.XPATH, '//div[@id="createModal"]//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4) 

        # === 6. VALIDASI (ASSERTION) ===
        # Memastikan kita tetap di halaman info-board
        assert "info-board" in driver.current_url.lower()
        
        # Memastikan judul pengumuman yang baru dibuat muncul di dalam halaman (terbaca di tabel)
        assert "Pengumuman Maintenance Server" in driver.page_source
        
        print("Pengujian tambah data Info Board sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")