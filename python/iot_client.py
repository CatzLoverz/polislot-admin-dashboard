import asyncio
import websockets
import json
import ssl

# ============================================================
# KONFIGURASI
# ============================================================
# Cloudflare Tunnel mendukung WebSocket via HTTPS (WSS)
# Tunnel meneruskan traffic ke container Docker port 6001 (Reverb)
#
# PENTING: Agar Cloudflare Tunnel bisa meneruskan WebSocket,
# pastikan tunnel dikonfigurasi dengan 2 subdomain/service:
#   1. raihanatmaja.my.id       → http://localhost:8080  (Web App)
#   2. ws.raihanatmaja.my.id    → http://localhost:6001  (Reverb WS)
#
# Jika tunnel hanya 1 subdomain (mengarah ke port 8080/Web saja),
# maka client WebSocket Python ini TIDAK bisa konek.
# Namun browser tetap bisa konek via Reverb internal.
# ============================================================

REVERB_APP_KEY = "xcubvd4inm14ayepjhro"

# Opsi 1: Koneksi via Cloudflare Tunnel (WSS) — butuh subdomain khusus WS
# WS_URL = f"wss://ws.raihanatmaja.my.id/app/{REVERB_APP_KEY}?protocol=7&client=python-iot"

# Opsi 2: Koneksi langsung via IP jaringan lokal (WS) — jika Raspi & server satu LAN
# WS_URL = f"ws://<IP_WSL>:6001/app/{REVERB_APP_KEY}?protocol=7&client=python-iot"

# Default: Cloudflare Tunnel (gunakan Opsi 2 jika satu jaringan lokal)
WS_URL = f"wss://ws.raihanatmaja.my.id/app/{REVERB_APP_KEY}?protocol=7&client=python-iot"

# Mac Address of the IoT Device (Mocked for testing)
MAC_ADDRESS = "00:1A:2B:3C:4D:5E"
CLEAN_MAC = MAC_ADDRESS.replace(":", "")
# The channel we want to subscribe to (must match the Laravel Event channel)
CHANNEL_NAME = f"iot.stream.{CLEAN_MAC}"

async def connect_and_communicate():
    print(f"Connecting to {WS_URL}...")
    
    # SSL context untuk koneksi WSS via Cloudflare
    ssl_context = ssl.create_default_context()
    
    try:
        async with websockets.connect(WS_URL, ssl=ssl_context) as websocket:
            print("Connected to Laravel Reverb WebSocket!")
            
            # 1. Pusher Protocol: Subscribe to the channel
            subscribe_message = {
                "event": "pusher:subscribe",
                "data": {
                    "channel": CHANNEL_NAME
                }
            }
            await websocket.send(json.dumps(subscribe_message))
            print(f"Subscribed to channel: {CHANNEL_NAME}")
            
            # Create a task to listen for incoming messages from the server
            listen_task = asyncio.create_task(listen_for_messages(websocket))
            
            # Keep the main loop running, periodically sending "ping"
            while True:
                ping_message = {
                    "event": "pusher:ping",
                    "data": {}
                }
                await websocket.send(json.dumps(ping_message))
                print("[IoT -> Server] Sent Ping")
                
                await asyncio.sleep(5) # Wait 5 seconds before next ping
                
    except websockets.exceptions.ConnectionClosedError as e:
        print(f"Connection closed unexpectedly: {e}")
    except ConnectionRefusedError:
        print(f"Connection refused. Make sure Reverb is running and tunnel is active.")
    except Exception as e:
        print(f"An error occurred: {e}")

async def listen_for_messages(websocket):
    """Listens for incoming messages from the server."""
    try:
        async for message in websocket:
            data = json.loads(message)
            event_name = data.get("event")
            
            if event_name == "pusher:connection_established":
                print("[Server -> IoT] Connection established successfully.")
            elif event_name == "pusher_internal:subscription_succeeded":
                print("[Server -> IoT] Subscription confirmed.")
            elif event_name == "pusher:pong":
                print("[Server -> IoT] Received Pong")
            elif event_name == "stream.received":
                # This matches the broadcastAs() in IotStreamReceived.php
                payload = json.loads(data.get("data", "{}"))
                print(f"[Server -> IoT] Received data: {payload}")
            else:
                print(f"[Server -> IoT] Unknown Event '{event_name}': {data}")
                
    except websockets.exceptions.ConnectionClosed:
        print("Listening task stopped: Connection closed.")

if __name__ == "__main__":
    print("--- IoT Device Simulator ---")
    print(f"Device MAC: {MAC_ADDRESS}")
    asyncio.run(connect_and_communicate())
