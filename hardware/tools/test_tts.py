import os
import time
from gtts import gTTS
import pygame

def analisis_suara(teks):
    print("\n" + "="*40)
    print("      HASIL ANALISIS TEXT-TO-SPEECH")
    print("="*40)
    print(f"Teks: \"{teks}\"\n")

    # 1. Analisis Teks (Kata dan Huruf)
    # Memisahkan kalimat menjadi list kata berdasarkan spasi
    daftar_kata = teks.split()
    jumlah_kata = len(daftar_kata)
    
    if jumlah_kata == 0:
        print("Teks kosong! Silakan masukkan kata.")
        return

    # Menghitung huruf (tanpa menghitung spasi atau tanda baca jika tidak diinginkan, 
    # tapi di sini kita hitung murni karakter selain spasi)
    jumlah_huruf = len(teks.replace(" ", ""))
    jumlah_karakter = len(teks)

    print("[1] INFORMASI TEKS")
    print(f" - Jumlah Kata       : {jumlah_kata} kata")
    print(f" - Jumlah Huruf      : {jumlah_huruf} huruf (tanpa spasi)")
    print(f" - Total Karakter    : {jumlah_karakter} karakter (termasuk spasi)")

    # 2. Proses Text-to-Speech (Google TTS)
    file_temp = "temp_analisis.mp3"
    print("\n[2] PROSES GENERATE SUARA")
    print(" - Mengunduh suara dari Google TTS...")
    try:
        tts = gTTS(text=teks, lang='id')
        tts.save(file_temp)
        print(" - File MP3 berhasil dibuat.")
    except Exception as e:
        print(f" ❌ ERROR: Gagal membuat suara. Pastikan Raspi terkoneksi internet. ({e})")
        return

    # 3. Pemutaran dan Perhitungan Waktu
    print("\n[3] PENGUKURAN DURASI AUDIO")
    pygame.mixer.init()
    pygame.mixer.music.load(file_temp)
    
    print(" - Memutar suara...")
    
    # Catat waktu persis sebelum suara mulai
    waktu_mulai = time.time()
    pygame.mixer.music.play()
    
    # Looping ini akan terus berjalan selama lagu masih berbunyi
    while pygame.mixer.music.get_busy():
        time.sleep(0.01) # Delay sangat kecil agar perhitungan waktu sangat presisi
        
    # Catat waktu persis setelah suara berhenti
    waktu_selesai = time.time()
    
    # 4. Kalkulasi Akhir
    durasi_total = waktu_selesai - waktu_mulai
    durasi_per_kata = durasi_total / jumlah_kata
    
    print(f" - Durasi Total      : {durasi_total:.3f} detik")
    print(f" - Rata-rata / Kata  : {durasi_per_kata:.3f} detik per kata")
    
    print("="*40)

    # Bersihkan file MP3 sementara agar tidak menumpuk di memori
    if os.path.exists(file_temp):
        os.remove(file_temp)
        pygame.mixer.quit()

if __name__ == "__main__":
    try:
        while True:
            input_teks = input("\nMasukkan kalimat yang ingin diuji (atau tekan Ctrl+C untuk keluar):\n>> ")
            analisis_suara(input_teks)
    except KeyboardInterrupt:
        print("\n\nKeluar dari program pengujian TTS.")