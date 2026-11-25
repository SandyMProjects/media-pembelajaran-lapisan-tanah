# ==============================================================================
# SCRIPT PENGUJIAN FUNGSIONAL HARDWARE (REVISI - CONTINUOUS & TAP DETECTION)
# ==============================================================================

import RPi.GPIO as GPIO
import time
import pygame
import speech_recognition as sr
from mfrc522 import SimpleMFRC522

# --- Konfigurasi Pin ---
BTN_KNG = 18   # Mode Belajar
BTN_RED = 17   # Mode Soal
BTN_PWR = 24   # Power
SENSOR_PINS = [5, 6, 12, 13, 16, 22] 
LED_PINS = [23, 19, 20, 26, 21, 27]

def setup():
    GPIO.setwarnings(False)
    GPIO.setmode(GPIO.BCM)
    
    # Setup Button (Pull Up -> Ditekan = LOW)
    GPIO.setup(BTN_KNG, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(BTN_RED, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(BTN_PWR, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    
    # Setup Sensor (Pull Down -> Disentuh = HIGH) & LED
    for pin in SENSOR_PINS:
        GPIO.setup(pin, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)
    for pin in LED_PINS:
        GPIO.setup(pin, GPIO.OUT)
        GPIO.output(pin, GPIO.LOW)

def matikan_led():
    for pin in LED_PINS:
        GPIO.output(pin, GPIO.LOW)

# --- 1. Pengujian RFID ---
def test_rfid():
    print("\n[TEST] Mode RFID berjalan terus-menerus...")
    print("Tekan Ctrl+C untuk kembali ke menu.")
    reader = SimpleMFRC522()
    try:
        while True:
            # UBAH DISINI: Gunakan read_id() agar hanya membaca UID dan tidak membaca teks
            id_kartu = reader.read_id() 
            
            print(f"✅ UID Terbaca: {id_kartu}")
            time.sleep(1.5) # Jeda agar tidak terlalu spam jika kartu ditahan
    except KeyboardInterrupt:
        print("\n>> Keluar dari Test RFID.")

# --- 2. Pengujian Sensor Sentuh & LED (Mendeteksi TAP) ---
def test_sensor_led():
    print("\n[TEST] Sentuh sensor 1-6 (Harus di-TAP, ditahan tidak akan spam).")
    print("Tekan Ctrl+C untuk kembali ke menu.")
    
    # Variabel untuk menyimpan status sensor sebelumnya
    status_sebelumnya = [GPIO.LOW] * 6 
    
    try:
        while True:
            for i in range(6):
                status_sekarang = GPIO.input(SENSOR_PINS[i])
                
                # Cek jika status SEKARANG HIGH (disentuh) TAPI status SEBELUMNYA LOW (belum disentuh)
                if status_sekarang == GPIO.HIGH and status_sebelumnya[i] == GPIO.LOW:
                    print(f"✅ Sensor {i+1} disentuh! LED {i+1} menyala.")
                    GPIO.output(LED_PINS[i], GPIO.HIGH)
                    time.sleep(0.2) # Memberi visualisasi LED menyala sesaat
                    GPIO.output(LED_PINS[i], GPIO.LOW)
                
                # Perbarui status sebelumnya
                status_sebelumnya[i] = status_sekarang
                
            time.sleep(0.05) # Delay kecil untuk stabilitas CPU
    except KeyboardInterrupt:
        matikan_led()
        print("\n>> Keluar dari Test Sensor.")

# --- 3. Pengujian Tombol (Mendeteksi TAP) ---
def test_tombol():
    print("\n[TEST] Tekan tombol Kuning, Merah, atau Power (Harus di-TAP).")
    print("Tekan Ctrl+C untuk kembali ke menu.")
    
    # Variabel untuk menyimpan status tombol sebelumnya (Default HIGH karena Pull-Up)
    last_kng = GPIO.HIGH
    last_red = GPIO.HIGH
    last_pwr = GPIO.HIGH
    
    try:
        while True:
            curr_kng = GPIO.input(BTN_KNG)
            curr_red = GPIO.input(BTN_RED)
            curr_pwr = GPIO.input(BTN_PWR)
            
            # Deteksi perubahan dari HIGH (dilepas) menjadi LOW (ditekan)
            if curr_kng == GPIO.LOW and last_kng == GPIO.HIGH:
                print("✅ Tombol KUNING (Belajar) di tekan!")
                
            if curr_red == GPIO.LOW and last_red == GPIO.HIGH:
                print("✅ Tombol MERAH (Soal) di tekan!")
                
            if curr_pwr == GPIO.LOW and last_pwr == GPIO.HIGH:
                print("✅ Tombol POWER di tekan!")
            
            # Simpan status saat ini untuk putaran loop berikutnya
            last_kng = curr_kng
            last_red = curr_red
            last_pwr = curr_pwr
            
            time.sleep(0.05) # Debounce delay
    except KeyboardInterrupt:
        print("\n>> Keluar dari Test Tombol.")

# --- 4. Pengujian Audio (Speaker) ---
def test_audio():
    import os
    print("\n[TEST] Memutar file audio dari folder sistem...")
    print("Tekan Ctrl+C untuk kembali ke menu.")
    
    # Path folder disamakan dengan script utama
    PATH_AUDIO = "/var/www/html/skripsi_tanah/assets/audio"
    
    # Pilih salah satu file mp3 yang pasti ada di folder kamu untuk dites
    file_test = "test.mp3" 
    full_path = os.path.join(PATH_AUDIO, file_test)
    
    # Cek apakah file benar-benar ada di direktori
    if not os.path.exists(full_path):
        print(f"\n❌ ERROR: File '{file_test}' tidak ditemukan!")
        print(f"Pastikan file ada di dalam folder: {PATH_AUDIO}")
        time.sleep(2)
        return

    pygame.mixer.init()
    
    try:
        while True:
            print(f"▶️ Sedang memutar: {file_test} ...")
            pygame.mixer.music.load(full_path)
            pygame.mixer.music.play()
            
            # Tahan program / looping selama lagu masih berbunyi
            while pygame.mixer.music.get_busy():
                time.sleep(0.1)
                
            print("✅ Audio selesai. Jeda 3 detik sebelum mengulang...")
            time.sleep(3)
            
    except KeyboardInterrupt:
        pygame.mixer.music.stop() # Matikan suara secara paksa jika di-cut
        print("\n>> Keluar dari Test Audio.")
    except Exception as e:
        print(f"❌ Error Audio: {e}")

# --- 5. Pengujian Mikrofon (Speech Recognition) ---
def test_mic():
    print("\n[TEST] Mikrofon standby terus-menerus.")
    print("Tekan Ctrl+C untuk kembali ke menu.")
    recognizer = sr.Recognizer()
    
    try:
        with sr.Microphone() as source:
            print("Menyesuaikan noise latar... (Mohon diam 1 detik)")
            recognizer.adjust_for_ambient_noise(source, duration=1)
            
            while True:
                print("\n Silakan bicara sesuatu...")
                try:
                    audio = recognizer.listen(source, timeout=3, phrase_time_limit=5)
                    print("Memproses suara...")
                    hasil = recognizer.recognize_google(audio, language="id-ID")
                    print(f"✅ Suara tertangkap: '{hasil}'")
                except sr.WaitTimeoutError:
                    print("... (Tidak ada suara, mendengarkan kembali)")
                except sr.UnknownValueError:
                    print("❌ Suara kurang jelas, coba lagi.")
    except KeyboardInterrupt:
        print("\n>> Keluar dari Test Mikrofon.")
    except Exception as e:
        print(f"\n❌ Error Hardware Mic: {e}")

# --- MENU UTAMA ---
def main_menu():
    setup()
    while True:
        print("\n" + "="*40)
        print("  MENU PENGUJIAN SISTEM (BAB 4)")
        print("="*40)
        print("1. Uji RFID Reader (Loop)")
        print("2. Uji Sensor Sentuh & LED (Mode Tap)")
        print("3. Uji Push Button (Mode Tap)")
        print("4. Uji Speaker / Audio (Looping Beep)")
        print("5. Uji Mikrofon (Continuous Voice)")
        print("0. Keluar")
        print("="*40)
        
        pilihan = input("Pilih nomor pengujian (0-5): ")
        
        if pilihan == '1': test_rfid()
        elif pilihan == '2': test_sensor_led()
        elif pilihan == '3': test_tombol()
        elif pilihan == '4': test_audio()
        elif pilihan == '5': test_mic()
        elif pilihan == '0':
            print("Keluar dari program pengujian.")
            GPIO.cleanup()
            break
        else:
            print("Pilihan tidak valid.")

if __name__ == "__main__":
    try:
        main_menu()
    except KeyboardInterrupt:
        GPIO.cleanup()
        print("\nProgram dihentikan.")