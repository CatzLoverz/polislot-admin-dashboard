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

def test_filter_verify_reward(driver):
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

        # === 2. MENUJU HALAMAN VERIFIKASI REWARD ==
        link_verify = driver.find_element(By.XPATH, '//a[contains(@href, "rewards/verify")]')
        driver.execute_script("arguments[0].click();", link_verify)
        time.sleep(3)

        # === 3. UJI FITUR TERAPKAN FILTER ===
        # Memilih "Accepted" dari dropdown Status
        filter_status = Select(driver.find_element(By.ID, "filter_status"))
        filter_status.select_by_value("accepted")
        time.sleep(1)

        # Memilih "Voucher" dari dropdown Tipe
        filter_type = Select(driver.find_element(By.ID, "filter_type"))
        filter_type.select_by_value("Voucher")
        time.sleep(1)

        # Klik tombol Terapkan Filter
        btn_apply = driver.find_element(By.ID, "apply_filter")
        btn_apply.click()
        time.sleep(3) # Tunggu DataTables memuat ulang data dari AJAX

        # === 4. UJI FITUR BATAL / RESET FILTER ===
        btn_reset = driver.find_element(By.ID, "reset_filter")
        btn_reset.click()
        time.sleep(3) # Tunggu DataTables kembali ke kondisi default

        # === 5. VALIDASI RESET ===
        # Memastikan dropdown status kembali ke "pending" (sesuai kode jQuery Anda)
        status_value_after_reset = driver.find_element(By.ID, "filter_status").get_attribute("value")
        assert status_value_after_reset == "pending"
        
        # Memastikan dropdown tipe kembali kosong
        type_value_after_reset = driver.find_element(By.ID, "filter_type").get_attribute("value")
        assert type_value_after_reset == ""

        print("Pengujian fitur Filter Verifikasi Reward sukses.")

    except Exception as e:
        pytest.fail(f"Terjadi kesalahan: {e}")