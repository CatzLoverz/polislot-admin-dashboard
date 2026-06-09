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

def test_edit_mission(driver):
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

        # === 3. KLIK TOMBOL EDIT DI TABEL ===
        btn_edit = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.CSS_SELECTOR, '#tableMission tbody tr:first-child .btn-edit'))
        )
        btn_edit.click()
        time.sleep(2)

        # === 4. UBAH DATA ===
        judul_input = driver.find_element(By.NAME, "mission_title")
        judul_input.clear()
        time.sleep(1)
        judul_update = "Misi Diubah ke Sequence"
        judul_input.send_keys(judul_update)
        
        # Ubah tipe menjadi SEQUENCE
        tipe_misi = Select(driver.find_element(By.ID, "inputType"))
        tipe_misi.select_by_value("SEQUENCE")
        time.sleep(2) # Tunggu efek jQuery muncul (divConsecutive)

        # Centang checkbox "Harus Berurut?" yang baru muncul
        checkbox_consecutive = driver.find_element(By.NAME, "mission_is_consecutive")
        if not checkbox_consecutive.is_selected():
            driver.execute_script("arguments[0].click();", checkbox_consecutive)
        time.sleep(2)

        # === 5. SIMPAN ===
        btn_simpan = driver.find_element(By.XPATH, '//div[@id="modalMission"]//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4)

        # === 6. VALIDASI ===
        assert judul_update in driver.page_source
        print("Pengujian ubah data Misi sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")