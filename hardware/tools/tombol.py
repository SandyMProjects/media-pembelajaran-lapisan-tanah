import os
import time
from gpiozero import Button
from signal import pause

# Tentukan pin GPIO yang digunakan untuk tombol power 
# (Ganti angka 21 dengan PIN GPIO yang Anda gunakan pada alat)
PIN_TOMBOL = 24 

# Inisialisasi tombol dengan batas waktu tahan (hold_time) 5 detik
tombol_power = Button(PIN_TOMBOL, hold_time=5)

waktu_tekan = 0

def saat_ditekan():
    global waktu_tekan
    waktu_tekan = time.time()
    print("[INFO] Tombol ditekan, menghitung waktu...")

def saat_dilepas():
    global waktu_tekan
    # Hitung durasi tombol dari saat ditekan hingga dilepas
    durasi = time.time() - waktu_tekan
    
    # Jika dilepas sebelum 5 detik, maka masuk logika Reset
    if durasi < 5.0:
        print(f"[AKSI] Tombol dilepas dalam {durasi:.2f} detik (< 5 detik).")
        print("[EKSEKUSI] Melakukan RESET (Reboot) sistem...\n")
        
        # Hapus tanda '#' pada baris di bawah ini jika ingin mengeksekusi reboot asli
        # os.system("sudo reboot")

def saat_ditahan():
    # Fungsi ini otomatis terpanggil ketika tombol ditahan tepat mencapai 5 detik
    print("[AKSI] Tombol telah ditahan selama 5 detik!")
    print("[EKSEKUSI] Melakukan SHUTDOWN sistem...\n")
    
    # Hapus tanda '#' pada baris di bawah ini jika ingin mengeksekusi shutdown asli
    # os.system("sudo shutdown -h now")

# Memetakan fungsi di atas ke dalam event tombol
tombol_power.when_pressed = saat_ditekan
tombol_power.when_released = saat_dilepas
tombol_power.when_held = saat_ditahan

# Menampilkan informasi di shell saat program pertama kali dijalankan
print("=== Program Pengetesan Tombol Power Dimulai ===")
print(f"Memantau GPIO {PIN_TOMBOL}...")
print(" -> Tekan Singkat (< 5 detik) = RESET")
print(" -> Tekan Lama (>= 5 detik)  = SHUTDOWN")
print("Tekan CTRL+C di keyboard untuk menghentikan program.\n")

# pause() digunakan agar program tetap menyala (standby) menunggu event tombol
pause()