import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class PoliSlotLoginTest(unittest.TestCase):
    
    def setUp(self):
        # 1. Inisialisasi Browser (Menggunakan Chrome)
        self.driver = webdriver.Chrome()
        self.driver.maximize_window()
        
        # 2. Buka halaman login lokal aplikasi
        # Sesuaikan URL dan port dengan output 'php artisan serve' Anda
        self.driver.get("http://raihanatmaja.my.id/login-form")

    def test_01_login_sukses(self):
        driver = self.driver
        
        # 3. Cari elemen form menggunakan ID dari file Blade Anda
        email_input = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.ID, "email"))
        )
        password_input = driver.find_element(By.ID, "password")
        
        # Tombol submit dicari menggunakan XPath karena tidak memiliki ID
        submit_button = driver.find_element(By.XPATH, "//button[@type='submit']")

        # 4. Masukkan kredensial valid (Ganti dengan data asli di database Anda)
        email_input.send_keys("akunyt123gue@gmail.com") 
        password_input.send_keys("Testing_12")     
        submit_button.click()

        # 5. Validasi: Pastikan URL berubah ke halaman dashboard setelah sukses
        WebDriverWait(driver, 10).until(
            EC.url_contains("/dashboard") 
        )
        self.assertIn("/dashboard", driver.current_url)

    def test_02_login_gagal_kredensial_salah(self):
        import time # Tambahkan import time di sini (atau di atas)
        driver = self.driver
        
        email_input = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.ID, "email"))
        )
        password_input = driver.find_element(By.ID, "password")
        submit_button = driver.find_element(By.XPATH, "//button[@type='submit']")

        # Masukkan kredensial yang salah
        email_input.send_keys("user_salah@domain.com")
        password_input.send_keys("passwordsalah")
        submit_button.click()
        time.sleep(5) 

    def tearDown(self):
        # 6. Tutup browser setelah setiap pengujian selesai
        self.driver.quit()

if __name__ == "__main__":
    unittest.main()