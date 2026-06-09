import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
import time

@pytest.fixture(scope="module")
def driver():
    driver = webdriver.Chrome()
    driver.maximize_window()
    yield driver
    driver.quit()

def test_edit_reward(driver):
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

        # === 2. MENUJU HALAMAN REWARDS ===
        link_reward = driver.find_element(By.XPATH, '//a[contains(@href, "rewards")]')
        driver.execute_script("arguments[0].click();", link_reward)
        time.sleep(3)

        # === 3. KLIK TOMBOL EDIT DI BARIS PERTAMA TABEL ===
        btn_edit = driver.find_element(By.CSS_SELECTOR, '#tableReward tbody tr:first-child .btn-edit')
        btn_edit.click()
        time.sleep(2) # Tunggu modal terbuka dan form terisi oleh JS

        # === 4. UBAH DATA DI DALAM MODAL ===
        nama_reward_input = driver.find_element(By.NAME, "reward_name")
        nama_reward_input.clear()
        time.sleep(1)
        nama_reward_update = "Voucher Kantin 100k (Updated)"
        nama_reward_input.send_keys(nama_reward_update)
        
        tipe_reward_dropdown = Select(driver.find_element(By.NAME, "reward_type"))
        tipe_reward_dropdown.select_by_value("Barang")
        time.sleep(1)

        poin_input = driver.find_element(By.NAME, "reward_point_required")
        poin_input.clear()
        time.sleep(1)
        poin_input.send_keys("250")
        time.sleep(2)

        # === 5. KLIK TOMBOL SIMPAN ===
        btn_simpan = driver.find_element(By.XPATH, '//div[@id="modalReward"]//button[@type="submit"]')
        btn_simpan.click()
        time.sleep(4)

        # === 6. VALIDASI ===
        assert nama_reward_update in driver.page_source
        print("Pengujian ubah data Reward sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")