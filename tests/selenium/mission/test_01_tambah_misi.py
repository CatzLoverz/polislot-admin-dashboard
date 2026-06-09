import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
import time

@pytest.fixture(scope="module")
def driver():
    driver = webdriver.Chrome()
    driver.maximize_window()
    yield driver
    driver.quit()

def test_add_mission(driver):
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

        # === 3. KLIK TOMBOL TAMBAH MISI ===
        btn_tambah = driver.find_element(By.ID, "btnAdd")
        btn_tambah.click()
        time.sleep(2) # Tunggu modal terbuka

        # === 4. ISI FORM MISI ===
        judul_input = driver.find_element(By.NAME, "mission_title")
        judul_misi_baru = "Parkir 10 Kali di Kampus"
        judul_input.send_keys(judul_misi_baru)
        time.sleep(1)

        poin_input = driver.find_element(By.NAME, "mission_points")
        poin_input.send_keys("500")
        time.sleep(1)

        # Memilih Tipe Misi (Target)
        tipe_misi = Select(driver.find_element(By.ID, "inputType"))
        tipe_misi.select_by_value("TARGET")
        time.sleep(1)

        threshold_input = driver.find_element(By.NAME, "mission_threshold")
        threshold_input.send_keys("10")
        time.sleep(1)

        deskripsi_input = driver.find_element(By.NAME, "mission_description")
        deskripsi_input.send_keys("Lakukan validasi parkir sebanyak 10 kali untuk mendapatkan koin.")
        time.sleep(2)

        # === 5. KLIK TOMBOL SIMPAN ===
        btn_simpan = driver.find_element(By.XPATH, '//div[@id="modalMission"]//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4) 

        # === 6. VALIDASI ===
        assert judul_misi_baru in driver.page_source
        print("Pengujian tambah data Misi sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")