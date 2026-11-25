from gtts import gTTS

generateTxt = "Jawab dengan suara setelah bunyi bip."

tts = gTTS(text=generateTxt, lang='id')
tts.save("/var/www/html/skripsi_tanah/assets/audio/instruksi_suara.mp3")
print("File mp3 berhasil dibuat!")
