import mysql.connector
from gtts import gTTS
import os
import time

# --- KONFIGURASI ---
PATH_AUDIO = "/var/www/html/skripsi_tanah/assets/audio"
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'root',
    'database': 'db_skripsi_tanah'
}

def generate_massal():
    print("="*50)
    print("  GENERATE AUDIO SOAL (VERSI PENJELASAN)")
    print("="*50)
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT * FROM soal")
        semua_soal = cursor.fetchall()
        conn.close()
        
        if not semua_soal:
            print(">> Database kosong. Tidak ada soal yang diproses.")
            return

        print(f">> Ditemukan {len(semua_soal)} soal di database. Memulai proses...\n")
        
        berhasil = 0
        gagal = 0

        for row in semua_soal:
            id_soal = row['id_soal']
            pertanyaan = row['pertanyaan_teks']
            
            # AMBIL TEKS PENJELASAN DARI DATABASE
            # (Ubah tulisan 'penjelasan' di bawah ini jika nama kolom di database Mas Sandy berbeda, misal 'isi_penjelasan')
            teks_penjelasan = str(row['penjelasan']) 
            
            file_soal = os.path.join(PATH_AUDIO, f"soal_{id_soal}.mp3")
            file_kunci = os.path.join(PATH_AUDIO, f"kunci_{id_soal}.mp3")
            
            print(f"Memproses Soal ID {id_soal}...", end=" ")
            
            try:
                # 1. Generate Audio Pertanyaan (Jika belum ada)
                if not os.path.exists(file_soal):
                    tts_soal = gTTS(text=pertanyaan, lang='id')
                    tts_soal.save(file_soal)
                
                # 2. Generate Audio Penjelasan Jawaban (PENGGANTI KUNCI)
                # Jika file belum ada, robot akan membacakan teks_penjelasan
                if not os.path.exists(file_kunci):
                    tts_kunci = gTTS(text=teks_penjelasan, lang='id')
                    tts_kunci.save(file_kunci)
                
                print("[SUKSES]")
                berhasil += 1
                
                time.sleep(1) # Jeda agar tidak diblokir Google
                
            except Exception as e:
                print(f"[GAGAL] -> Error: {e}")
                gagal += 1
                
        print("\n" + "="*50)
        print(f"PROSES SELESAI! Berhasil: {berhasil} | Gagal: {gagal}")
        print("="*50)

    except Exception as e:
        print(f"[ERROR DB] Gagal terhubung ke database: {e}")

if __name__ == "__main__":
    generate_massal()