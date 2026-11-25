# ==============================================================================
# Media Pembelajaran Lapisan Tanah Dengan Menerapkan Algoritma Adaptive Learning
# ==============================================================================
# --- 1. SUPPRESS ERROR ALSA ---
from ctypes import *
try:
    ERROR_HANDLER_FUNC = CFUNCTYPE(None, c_char_p, c_int, c_char_p, c_int, c_char_p)
    def py_error_handler(filename, line, function, err, fmt):
        pass 
    c_error_handler = ERROR_HANDLER_FUNC(py_error_handler)
    asound = cdll.LoadLibrary('libasound.so')
    asound.snd_lib_error_set_handler(c_error_handler)
except:
    pass

# --- 2. Imports Library---
import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522
import pygame
import time
import sys
import os
import random
from gtts import gTTS
import speech_recognition as sr
import mysql.connector
import threading

# --- 3. Konfigurasi Path, Database & Mic (Dari config.py) ---
try:
    from config import PATH_AUDIO, DB_CONFIG, MIC_INDEX, FILE_TEMP
except ImportError:
    raise FileNotFoundError(
        "File 'hardware/config.py' tidak ditemukan. "
        "Salin 'hardware/config.example.py' menjadi 'hardware/config.py' dan sesuaikan konfigurasinya."
    )

# --- 4. Konfigurasi Pin ---
#       4.1. Button
BTN_KNG = 18   # Mode Belajar
BTN_RED = 17   # Mode Soal
BTN_PWR = 24   # Reset / Shutdown

#       4.2. Sensor Sentuh (Urut: O, A, E, B, C, R)
SENSOR_PINS = [5, 6, 12, 13, 16, 22] 

#       4.3. LED (Urut sesuai sensor)
LED_PINS = [23, 19, 20, 26, 21, 27]

# --- 7. Setup Awal ---
pygame.mixer.init()

# Mengatur mode pin Raspberry Pi dan inisialisasi input/output (Tombol, Sensor, LED)
def setup_gpio():
    GPIO.setwarnings(False)
    try:
        GPIO.cleanup()
    except:
        pass
        
    GPIO.setmode(GPIO.BCM)

    # Setup Button (Pull Up)
    GPIO.setup(BTN_KNG, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(BTN_RED, GPIO.IN, pull_up_down=GPIO.PUD_UP)
    GPIO.setup(BTN_PWR, GPIO.IN, pull_up_down=GPIO.PUD_UP)

    # Setup Sensor (Pull Down)
    for pin in SENSOR_PINS:
        GPIO.setup(pin, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)

    # Setup LED (Output)
    for pin in LED_PINS:
        GPIO.setup(pin, GPIO.OUT)
        GPIO.output(pin, GPIO.LOW)

# Mematikan seluruh lampu indikator LED pada alat
def matikan_semua_led():
    for pin in LED_PINS:
        GPIO.output(pin, GPIO.LOW)

# Menarik data soal dari database dan memisahkannya berdasarkan 3 level kesulitan
def load_soal_dari_db():
    print(">> Menghubungkan ke Database untuk ambil soal...")
    global BANK_SOAL
    BANK_SOAL = {1: [], 2: [], 3: []}
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM soal")
        results = cursor.fetchall()
        
        jumlah_soal = 0
        
        for row in results:
            id_soal = row['id_soal']
            level_str = row['level']
            kunci_str = str(row['kunci_jawaban']).lower().strip() 
            
            level_int = 1
            if level_str == 'sedang': level_int = 2
            elif level_str == 'susah': level_int = 3
            
            if kunci_str.startswith("sensor_"):
                tipe_soal = 'sentuh'
                try: kunci_final = int(kunci_str.split('_')[1])
                except: kunci_final = -1
            else:
                tipe_soal = 'suara'
                kunci_final = kunci_str
            
            if kunci_final != -1:
                BANK_SOAL[level_int].append({
                    'id': id_soal, 
                    'tipe': tipe_soal, 
                    'kunci': kunci_final
                })
                jumlah_soal += 1
                
        print(f"[SUKSES] Berhasil memuat {jumlah_soal} soal dari Database.")
        conn.close()
        
    except Exception as e:
        print(f"[ERROR DB] Gagal ambil soal: {e}")

# Mencatat durasi waktu yang dihabiskan siswa saat membaca/mendengarkan materi
def simpan_log_belajar(id_siswa, durasi):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        sql = "INSERT INTO log_eksplorasi (id_siswa, waktu_selesai, durasi_detik) VALUES (%s, NOW(), %s)"
        cursor.execute(sql, (id_siswa, int(durasi)))
        conn.commit()
        conn.close()
        print(f"[DB] Log Belajar tersimpan: {durasi} detik.")
    except Exception as e:
        print(f"[ERROR DB] Gagal simpan log belajar: {e}")

# Menyimpan skor akhir, riwayat benar/salah, dan level tiap soal ke dalam database
def simpan_log_kuis(id_siswa, skor, detail_jawaban):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Simpan Sesi Utama
        sql_sesi = "INSERT INTO log_sesi (id_siswa, tanggal, skor_akhir, status) VALUES (%s, NOW(), %s, 'selesai')"
        cursor.execute(sql_sesi, (id_siswa, skor))
        id_sesi = cursor.lastrowid 
        
        # Simpan Detail Tiap Soal
        sql_detail = "INSERT INTO detail_log (id_sesi, id_soal, jawaban_siswa, status_jawaban, level_saat_itu, waktu_jawab) VALUES (%s, %s, %s, %s, %s, NOW())"
        
        for d in detail_jawaban:
            val = (id_sesi, d['id_soal'], d['jawaban'], d['status'], d['level'])
            cursor.execute(sql_detail, val)
            
        conn.commit()
        conn.close()
        print(f"[DB] Log Kuis tersimpan. ID Sesi: {id_sesi}")
    except Exception as e:
        print(f"[ERROR DB] Gagal simpan log kuis: {e}")
        
def cek_level_terakhir_siswa(id_siswa):
    # Fungsi untuk mengecek level terakhir dari siswa di database
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        sql = """
        SELECT d.level_saat_itu 
        FROM detail_log d
        JOIN log_sesi s ON d.id_sesi = s.id_sesi
        WHERE s.id_siswa = %s
        ORDER BY d.id_detail DESC LIMIT 1
        """
        cursor.execute(sql, (id_siswa,))
        result = cursor.fetchone()
        conn.close()
        
        if result:
            level_str = result['level_saat_itu']
            if level_str == 'sedang': return 2
            elif level_str == 'susah': return 3
            else: return 1
        else:
            # Jika data tidak ada / siswa baru, mulai dari Mudah (1)
            return 1 
            
    except Exception as e:
        print(f"[ERROR DB] Gagal cek riwayat level: {e}")
        return 1

# Memutar file audio (.mp3) sebagai panduan instruksi atau feedback ke siswa
def putar_suara(nama_file, folder_path=PATH_AUDIO, tunggu=True):
    full_path = os.path.join(folder_path, nama_file)
    try:
        if pygame.mixer.music.get_busy():
            pygame.mixer.music.stop()
        
        pygame.mixer.music.load(full_path)
        pygame.mixer.music.play()
        
        if tunggu:
            while pygame.mixer.music.get_busy():
                time.sleep(0.1)
    except Exception as e:
        print(f"[ERROR AUDIO] Gagal memutar {nama_file}: {e}")

# Thread background untuk mengeksekusi Shutdown atau Reboot fisik via tombol
def pantau_tombol_power():
    while True:
        if GPIO.input(BTN_PWR) == GPIO.LOW:
            waktu_ditekan = time.time()
            durasi = 0
            
            while GPIO.input(BTN_PWR) == GPIO.LOW:
                durasi = time.time() - waktu_ditekan
            
                if durasi >= 5.0:
                    print("\n[SYSTEM] Mematikan Raspberry Pi (Shutdown)...")
                    putar_suara("shutdown.mp3", PATH_AUDIO, tunggu=True)
                    matikan_semua_led()
                    os.system("sudo shutdown now")
                    return
                
                time.sleep(0.1)
                    
            if durasi > 0.1:
                    print("\n[SYSTEM] Mereset Raspberry Pi (Reboot)...")
                    putar_suara("reboot.mp3", PATH_AUDIO, tunggu=True)
                    matikan_semua_led()
                    os.system("sudo reboot")
                
        time.sleep(0.1)

# =======================
# BAGIAN 1: MODE BELAJAR
# =======================
# MODE 1 (EKSPLORASI): Siswa menyentuh sensor untuk belajar materi lapisan tanah.
def mode_pembelajaran(id_siswa_aktif):
    print("\n" + "="*40)
    print("       MASUK MODE BELAJAR")
    print("="*40)
    
    matikan_semua_led()
    putar_suara("open_mode_belajar.mp3", PATH_AUDIO, tunggu=True)
    print("Silahkan sentuh sensor lapisan tanah...")

    waktu_mulai_belajar = time.time()
    waktu_terakhir_aktif = time.time()
    TIMEOUT_BELAJAR = 30 

    while True:
        # 1. HITUNG TIMEOUT
        durasi_diam = time.time() - waktu_terakhir_aktif
        if durasi_diam > TIMEOUT_BELAJAR:
            print("\n[TIMEOUT] Tidak ada aktivitas di Mode Belajar.")
            putar_suara("exit_belajar.mp3", PATH_AUDIO, tunggu=True)
            break

        # 2. PINDAH KE MODE SOAL
        # Jika tombol merah ditekan (LOW karena Pull-Up)
        if GPIO.input(BTN_RED) == GPIO.LOW:
            print("\n>> [INTERUPSI] Siswa ingin langsung pindah ke Mode Soal!")
            
            try:
                from gtts import gTTS
                teks_pindah = "Anda akan berpindah ke mode soal. Silahkan tempelkan kembali kartu Anda untuk konfirmasi."
                tts = gTTS(text=teks_pindah, lang='id')
                tts.save(os.path.join(PATH_AUDIO, "pindah_mode.mp3"))
                putar_suara("pindah_mode.mp3", PATH_AUDIO, tunggu=True)
            except:
                print(">> Gagal generate suara pindah mode dinamis.")
            
            print(">> Menunggu konfirmasi kartu RFID...")
            
            while True:
                try:
                    from mfrc522 import SimpleMFRC522
                    interupt_reader = SimpleMFRC522()
                    
                    id_kartu_cek = interupt_reader.read_id()
                    id_kartu_cek = str(id_kartu_cek).strip()
                    
                    conn = mysql.connector.connect(**DB_CONFIG)
                    curr = conn.cursor(dictionary=True)
                    curr.execute("SELECT * FROM siswa WHERE rfid_uid=%s", (id_kartu_cek,))
                    siswa_valid = curr.fetchone()
                    conn.close()
                    
                    if siswa_valid:
                        id_siswa_baru = siswa_valid['id_siswa']
                        print(f">> Konfirmasi RFID Berhasil! Mengalihkan {siswa_valid['nama_siswa']} ke Mode Soal...")
                        
                        total_durasi = time.time() - waktu_mulai_belajar
                        simpan_log_belajar(id_siswa_aktif, total_durasi)
                        
                        mode_soal(id_siswa_baru)
                        return
                    else:
                        print(">> Kartu tidak dikenal! Silahkan gunakan kartu yang terdaftar.")
                        putar_suara("belum_terdaftar.mp3", PATH_AUDIO, tunggu=True)
                except Exception as e:
                    print(f">> Error pembacaan RFID saat interupsinya: {e}")
                time.sleep(0.5)

        # 3. PANTAU SENSOR SENTUH
        for i in range(6):
            if GPIO.input(SENSOR_PINS[i]) == GPIO.HIGH:
                print(f">> Sensor {i+1} disentuh.")
                
                GPIO.output(LED_PINS[i], GPIO.HIGH)
                nama_file = f"materi_{i+1}.mp3"
                
                putar_suara(nama_file, PATH_AUDIO, tunggu=True) 
                
                GPIO.output(LED_PINS[i], GPIO.LOW)
                time.sleep(0.5)
                
                waktu_terakhir_aktif = time.time()

        time.sleep(0.1)
    
    total_durasi = time.time() - waktu_mulai_belajar
    simpan_log_belajar(id_siswa_aktif, total_durasi)


# ==============================================================================
# BAGIAN 2: MODE SOAL / KUIS
# ==============================================================================
# Menghidupkan LED dan memutar suara kunci jawaban yang benar setelah siswa menjawab
def tampilkan_jawaban_benar(kunci_jawaban, file_kunci):
    
    if isinstance(kunci_jawaban, int) and 0 <= kunci_jawaban <= 5:
        print(f">> Menyalakan LED {kunci_jawaban+1} sebagai instruksi jawaban yang benar.")
        GPIO.output(LED_PINS[kunci_jawaban], GPIO.HIGH)
        putar_suara(file_kunci, PATH_AUDIO, tunggu=True)
        GPIO.output(LED_PINS[kunci_jawaban], GPIO.LOW)
    else:
        print(f">> Membacakan penjelasan untuk jawaban suara: '{kunci_jawaban}'")
        putar_suara(file_kunci, PATH_AUDIO, tunggu=True)

# Membuat dan memutar rekapitulasi nilai akhir siswa menggunakan Text-to-Speech (gTTS)
def sebutkan_skor(benar, salah, skor, level_akhir):
    nama_level = "Mudah"
    if level_akhir == 2: nama_level = "Medium"
    elif level_akhir == 3: nama_level = "Sulit"

    teks = f"Kamu menjawab benar {benar} soal, salah {salah} soal. Skor akhir {skor}. Level terakhir kamu adalah {nama_level}."
    print("\n[REPORT] " + teks)
    try:
        tts = gTTS(text=teks, lang='id')
        tts.save(os.path.join(PATH_AUDIO, "hasil_akhir.mp3"))
        putar_suara("hasil_akhir.mp3", PATH_AUDIO, tunggu=True)
    except:
        print("Offline mode: Gagal generate suara skor.")

# MODE 2 (KUIS ADAPTIF): Mengajukan 10 soal acak (input suara/sentuh) dan menaik-turunkan level soal berdasarkan ketepatan jawaban.
def mode_soal(id_siswa_aktif):
    load_soal_dari_db()
    if not BANK_SOAL[1] and not BANK_SOAL[2] and not BANK_SOAL[3]:
        print("[WARNING] Bank Soal Kosong!")
        return
    
    print("\n" + "="*40)
    print("        MASUK MODE SOAL/KUIS")
    print("="*40)

    matikan_semua_led()

    recognizer = sr.Recognizer()
    try:
        mic = sr.Microphone(device_index=MIC_INDEX, sample_rate=44100, chunk_size=1024)
    except:
        mic = sr.Microphone(sample_rate=44100, chunk_size=1024)

    putar_suara("open_mode_soal.mp3", PATH_AUDIO, tunggu=True)
    time.sleep(1)

    skor = 0; total_benar = 0; total_salah = 0
    level_sekarang = cek_level_terakhir_siswa(id_siswa_aktif)
    
    txt_awal = "MUDAH" if level_sekarang == 1 else ("SEDANG" if level_sekarang == 2 else "SULIT")
    print(f">> Level awal ditetapkan: {txt_awal}")
    
    soal_terpakai = []
    detail_log_to_db = []

    for i in range(10):
        nomor_urut = i + 1
        
        calon_soal = [s for s in BANK_SOAL[level_sekarang] if s['id'] not in soal_terpakai]
        if not calon_soal:
            semua_soal = BANK_SOAL[1] + BANK_SOAL[2] + BANK_SOAL[3]
            calon_soal = [s for s in semua_soal if s['id'] not in soal_terpakai]
        if not calon_soal: break

        soal_aktif = random.choice(calon_soal)
        id_soal = soal_aktif['id']
        kunci_sekarang = soal_aktif['kunci']
        mode_input = soal_aktif['tipe']
        soal_terpakai.append(id_soal)

        file_soal = f"soal_{id_soal}.mp3"
        file_kunci = f"kunci_{id_soal}.mp3"

        txt_level = "MUDAH" if level_sekarang == 1 else ("SEDANG" if level_sekarang == 2 else "SULIT")
        print(f"\n" + "="*50)
        print(f"SOAL NOMOR   : {nomor_urut}")
        print(f"LEVEL SAAT INI: {txt_level}")
        print(f"ID SOAL DI DB: {id_soal}")
        print(f"MODE JAWAB   : {mode_input.upper()}")
        print(f"KUNCI JAWABAN: {kunci_sekarang}") 
        print("="*50)

        putar_suara(f"nomer_{nomor_urut}.mp3", PATH_AUDIO, tunggu=True)
        time.sleep(0.5)
        
        if mode_input == 'suara':
            putar_suara("instruksi_suara.mp3", PATH_AUDIO, tunggu=True)
        else:
            putar_suara("instruksi_sentuh.mp3", PATH_AUDIO, tunggu=True)
        
        putar_suara(file_soal, PATH_AUDIO, tunggu=True)

        percobaan = 0
        status_menjawab = False
        jawaban_teks_log = "timeout"

        while percobaan < 3:
            print(f">> Menunggu jawaban... (Percobaan {percobaan + 1})")
            waktu_mulai = time.time()
            input_terdeteksi = False
            jawaban_user = -1

            while True:
                durasi = time.time() - waktu_mulai
                if durasi > 15:
                    print("[TIMEOUT]")
                    putar_suara("waktu_menjawab_last.mp3", PATH_AUDIO, tunggu=True)
                    input_terdeteksi = True
                    break 

                if mode_input == 'sentuh':
                    for idx in range(6):
                        if GPIO.input(SENSOR_PINS[idx]) == GPIO.HIGH:
                            jawaban_user = idx
                            input_terdeteksi = True
                            break
                    if not input_terdeteksi: time.sleep(0.05)

                elif mode_input == 'suara':
                    try:
                        with sr.Microphone() as source:
                            print("\n>> Menyesuaikan noise latar belakang...")
                            recognizer.adjust_for_ambient_noise(source, duration=1)
                            putar_suara("bip.mp3", PATH_AUDIO, tunggu=True)
                            print(">> Silakan bicara sekarang!")
                            
                            try:
                                audio = recognizer.listen(source, timeout=5, phrase_time_limit=10)
                                print(">> Memproses suara...")
                                
                                hasil = recognizer.recognize_google(audio, language="id-ID")
                                jawaban_user = str(hasil).lower()
                                print(f"[TERDENGAR]: '{jawaban_user}'")
                                input_terdeteksi = True
                                
                            except sr.WaitTimeoutError:
                                print(">> Waktu habis, tidak ada suara terdeteksi.")
                            except sr.UnknownValueError:
                                print(">> Suara tidak jelas atau kata tidak dikenali.")
                            except sr.RequestError as e:
                                print(f">> Error Google Speech: {e}")
                    except Exception as e:
                        print(f">> Hardware Mic bermasalah: {e}")
                
                if input_terdeteksi: break
            
            if input_terdeteksi and jawaban_user != -1:
                if mode_input == 'sentuh':
                    is_benar = (jawaban_user == kunci_sekarang)
                    jawaban_teks_log = f"sensor_{jawaban_user}"
                else:
                    is_benar = (str(kunci_sekarang) in jawaban_user.split())
                    jawaban_teks_log = jawaban_user
                
                if is_benar:
                    print("\n>> BENAR!")
                    snd = "jawaban_benar_last.mp3" if nomor_urut == 10 else "jawaban_benar.mp3"
                    putar_suara(snd, PATH_AUDIO, tunggu=True)
                    status_menjawab = True
                    break
                else:
                    percobaan += 1
                    print(f"\n>> SALAH! ({percobaan}/3)")
                    putar_suara("jawaban_salah.mp3", PATH_AUDIO, tunggu=True)
                    if percobaan < 3:
                        sisa = 3 - percobaan
                        putar_suara(f"sisa_{sisa}.mp3", PATH_AUDIO, tunggu=True)
                        time.sleep(0.5)
            elif input_terdeteksi and jawaban_user == -1:
                percobaan += 1
                if percobaan < 3:
                    sisa = 3 - percobaan
                    putar_suara(f"sisa_{sisa}.mp3", PATH_AUDIO, tunggu=True)
                else:
                    putar_suara("jawaban_salah.mp3", PATH_AUDIO, tunggu=True)
                
        level_label = 'mudah' if level_sekarang == 1 else ('sedang' if level_sekarang==2 else 'susah')
        
        if status_menjawab:
            total_benar += 1; skor += 10
            detail_log_to_db.append({'id_soal': id_soal, 'jawaban': jawaban_teks_log, 'status': 'benar', 'level': level_label})
            if level_sekarang < 3: level_sekarang += 1
        else:
            total_salah += 1
            detail_log_to_db.append({'id_soal': id_soal, 'jawaban': jawaban_teks_log, 'status': 'salah', 'level': level_label})
            if level_sekarang > 1: level_sekarang -= 1
            
            tampilkan_jawaban_benar(kunci_sekarang, file_kunci)
            
            if nomor_urut < 10: 
                putar_suara("lanjut_soal.mp3", PATH_AUDIO, tunggu=True)
        time.sleep(1)

    print("\n--- SELESAI KUIS ---")
    putar_suara("exit_soal.mp3", PATH_AUDIO, tunggu=True)
    sebutkan_skor(total_benar, total_salah, skor, level_sekarang)
    simpan_log_kuis(id_siswa_aktif, skor, detail_log_to_db)

# ==============================================================================
# MAIN LOOP
# ==============================================================================
# LOOP UTAMA: Menunggu scan kartu RFID, mencocokkan data otentikasi siswa, lalu mengarahkan ke menu pilihan mode.
def main():
    setup_gpio()
    thread_power = threading.Thread(target=pantau_tombol_power, daemon=True)
    thread_power.start()

    try:
        while True:
            
            try:
                reader = SimpleMFRC522()
            except:
                pass
            
            matikan_semua_led()
            print("\n=============================================")
            print("    MEDIA PEMBELAJARAN LAPISAN TANAH (IoT)   ")
            print("=============================================")
            print(">> Menunggu Kartu RFID ditempelkan...")

            id_kartu, text = reader.read()
            id_kartu = str(id_kartu).strip()
            print(f"[RFID] Terbaca ID: {id_kartu}")
            
            try:
                conn = mysql.connector.connect(**DB_CONFIG)
                curr = conn.cursor(dictionary=True)
                
                sql_cek = """
                SELECT siswa.*, kelas.nama_kelas 
                FROM siswa 
                LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                WHERE siswa.rfid_uid = %s
                """
                curr.execute(sql_cek, (id_kartu,))
                siswa = curr.fetchone()
                conn.close()
            except Exception as e:
                print(f"Error Database: {e}")
                siswa = None

            if not siswa:
                print(">> Kartu TIDAK DIKENAL. Mengirim ID ke Web...")
                
                try:
                    with open(FILE_TEMP, 'w') as f:
                        f.write(id_kartu)
                    os.chmod(FILE_TEMP, 0o777)
                except: pass

                putar_suara("belum_terdaftar.mp3", PATH_AUDIO, tunggu=True) 
                time.sleep(1)
                continue 
                
            else:
                if os.path.exists(FILE_TEMP):
                    try: os.remove(FILE_TEMP)
                    except: pass

                nama_siswa = siswa['nama_siswa']
                id_siswa_db = siswa['id_siswa']
                kelas_siswa = siswa.get('nama_kelas', '') 
                
                teks_print = f">> Login Berhasil! Halo, {nama_siswa}"
                if kelas_siswa: teks_print += f" dari {kelas_siswa}"
                print(teks_print)
                
                teks_sapaan = f"Halo {nama_siswa}"
                if kelas_siswa: teks_sapaan += f" dari kelas {kelas_siswa}"
                
                try:
                    tts = gTTS(text=teks_sapaan, lang='id')
                    tts.save(os.path.join(PATH_AUDIO, "nama_dinamis.mp3"))
                    putar_suara("nama_dinamis.mp3", PATH_AUDIO, tunggu=True)
                except:
                    print(">> [Peringatan] Gagal membuat suara dinamis (Mungkin internet mati).")
                    
                putar_suara("sapaan.mp3", PATH_AUDIO, tunggu=False) 
                
                print("Silahkan Pilih Mode: Kuning (Belajar) / Merah (Soal)...")
                
                waktu_mulai = time.time()
                
                while True:
                    durasi = time.time() - waktu_mulai
                    if durasi > 30:
                        print("\n[TIMEOUT] Waktu pilih menu habis!")
                        putar_suara("timeout.mp3", PATH_AUDIO, tunggu=True)
                        break 
                    
                    if GPIO.input(BTN_KNG) == False:
                        print(">> Masuk Mode Belajar...")
                        mode_pembelajaran(id_siswa_db)
                        break 
                    
                    elif GPIO.input(BTN_RED) == False:
                        print(">> Masuk Mode Soal...")
                        mode_soal(id_siswa_db)
                        break 
                    
                    time.sleep(0.1)
                    
            time.sleep(2)

    except KeyboardInterrupt:
        print("\nProgram Dihentikan Pengguna.")
    
    finally:
        matikan_semua_led()
        GPIO.cleanup()
        print("GPIO Cleaned. Bye!")
        
if __name__ == "__main__":
    main()