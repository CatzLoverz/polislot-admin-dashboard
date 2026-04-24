import asyncio
import websockets
import json
import ssl
import sys

# ============================================================
# KONFIGURASI
# ============================================================
# Semua traffic (HTTP + WebSocket) lewat satu domain dan port yang sama.
# Caddy reverse proxy secara otomatis meneruskan /app/* ke Reverb internal.
#
# Koneksi via Cloudflare Tunnel (HTTPS/WSS):
#   wss://raihanatmaja.my.id/app/{key}?protocol=7
#
# Koneksi langsung via LAN (HTTP/WS):
#   ws://<IP_SERVER>:8080/app/{key}?protocol=7
# ============================================================

REVERB_APP_KEY = "xcubvd4inm14ayepjhro"

# --- Pilih salah satu mode koneksi ---

# Mode 1: Via Cloudflare Tunnel (default)
WS_HOST = "raihanatmaja.my.id"
WS_SCHEME = "wss"  # wss untuk HTTPS, ws untuk HTTP
WS_PORT = 443      # 443 untuk tunnel, 8080 untuk LAN

# Mode 2: Via LAN langsung (uncomment untuk pakai)
# WS_HOST = "192.168.1.100"  # Ganti dengan IP server
# WS_SCHEME = "ws"
# WS_PORT = 8080

# --- URL otomatis ---
# Port tidak ditampilkan jika default (443 untuk wss, 80 untuk ws)
if (WS_SCHEME == "wss" and WS_PORT == 443) or (WS_SCHEME == "ws" and WS_PORT == 80):
    WS_URL = f"{WS_SCHEME}://{WS_HOST}/app/{REVERB_APP_KEY}?protocol=7&client=python-iot"
else:
    WS_URL = f"{WS_SCHEME}://{WS_HOST}:{WS_PORT}/app/{REVERB_APP_KEY}?protocol=7&client=python-iot"

# Mac Address of the IoT Device
MAC_ADDRESS = "00:1A:2B:3C:4D:5E"
CLEAN_MAC = MAC_ADDRESS.replace(":", "")
CHANNEL_NAME = f"iot.stream.{CLEAN_MAC}"

async def connect_and_communicate():
    print(f"Connecting to {WS_URL}...")
    
    # SSL context untuk WSS, None untuk WS
    ssl_context = ssl.create_default_context() if WS_SCHEME == "wss" else None
    
    try:
        async with websockets.connect(WS_URL, ssl=ssl_context) as websocket:
            print("Connected to Laravel Reverb WebSocket!")
            
            # Pusher Protocol: Subscribe to the channel
            subscribe_message = {
                "event": "pusher:subscribe",
                "data": {
                    "channel": CHANNEL_NAME
                }
            }
            await websocket.send(json.dumps(subscribe_message))
            print(f"Subscribed to channel: {CHANNEL_NAME}")
            
            listen_task = asyncio.create_task(listen_for_messages(websocket))
            
            while True:
                ping_message = {
                    "event": "pusher:ping",
                    "data": {}
                }
                await websocket.send(json.dumps(ping_message))
                print("[IoT -> Server] Sent Ping")
                
                await asyncio.sleep(5)
                
    except websockets.exceptions.ConnectionClosedError as e:
        print(f"Connection closed unexpectedly: {e}")
    except ConnectionRefusedError:
        print(f"Connection refused. Make sure server is running at {WS_HOST}:{WS_PORT}")
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
                payload = json.loads(data.get("data", "{}"))
                frame = payload.get("frameData", "")
                if frame.startswith("data:image"):
                    print(f"[Server -> IoT] Received image frame ({len(frame)} chars)")
                else:
                    print(f"[Server -> IoT] Received text: {frame}")
            else:
                print(f"[Server -> IoT] Event '{event_name}': {data}")
                
    except websockets.exceptions.ConnectionClosed:
        print("Listening task stopped: Connection closed.")

if __name__ == "__main__":
    print("--- IoT Device Simulator (WebSocket Client) ---")
    print(f"Device MAC: {MAC_ADDRESS}")
    print(f"Mode: {WS_SCHEME.upper()} | Host: {WS_HOST}:{WS_PORT}")
    asyncio.run(connect_and_communicate())
