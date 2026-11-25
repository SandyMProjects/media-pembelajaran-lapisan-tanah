import os

# Path dasar otomatis mengikuti lokasi folder proyek
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PATH_AUDIO = os.path.join(BASE_DIR, "assets", "audio")
FILE_TEMP = os.path.join(BASE_DIR, "temp_rfid.txt")

# Konfigurasi Database MySQL (Salin file ini menjadi config.py dan sesuaikan)
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'PASSWORD_DATABASE_ANDA',
    'database': 'db_skripsi_tanah'
}

# Indeks perangkat USB Soundcard / Mikrofon pada Raspberry Pi
MIC_INDEX = 2