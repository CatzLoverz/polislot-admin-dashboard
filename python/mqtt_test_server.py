import paho.mqtt.client as mqtt
import json
import base64
import os

# Konfigurasi MQTT Server
BROKER = "127.0.0.1"
PORT = 1883  # Laravel/Server biasa mendengarkan lewat TCP langsung, lebih ringan
TOPIC_SUBSCRIBE = "polislot/device/#"  # Subscribe ke semua topik device

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("✅ Server Terhubung ke Broker MQTT (TCP)")
        client.subscribe(TOPIC_SUBSCRIBE)
        print(f"Mendengarkan di topik: {TOPIC_SUBSCRIBE} ...\n")
    else:
        print(f"❌ Gagal terhubung, return code {rc}")

def on_message(client, userdata, msg):
    print(f"📥 Pesan diterima di topik: {msg.topic}")
    
    try:
        payload = json.loads(msg.payload.decode('utf-8'))
        
        # Jika ini adalah pesan status
        if msg.topic.endswith("/status"):
            print(f"   [STATUS] Kendaraan: {payload.get('vehicle_count')} | Status: {payload.get('status')}")
            
        # Jika ini adalah pesan gambar/snapshot
        elif msg.topic.endswith("/snapshot"):
            print(f"   [GAMBAR] Menerima gambar snapshot dari {payload.get('mac_address')}...")
            img_data = base64.b64decode(payload.get('image_base64'))
            
            # Simpan gambar
            filename = "received_snapshot.jpg"
            with open(filename, "wb") as f:
                f.write(img_data)
            print(f"   📸 Gambar berhasil disimpan sebagai '{filename}' di folder python.")
            
    except Exception as e:
        print(f"   ⚠️ Error memproses payload: {e}")
    print("-" * 40)

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

print(f"Memulai MQTT Listener Server di tcp://{BROKER}:{PORT} ...")
client.connect(BROKER, PORT, 60)

# Loop selamanya (seperti queue-worker/daemon)
client.loop_forever()
