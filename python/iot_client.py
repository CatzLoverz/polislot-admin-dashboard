import asyncio
import websockets
import json
import time
import socket

# Configuration
# Replace this with your actual Laravel Reverb WebSocket URL and Port.
# Default Reverb usually runs on port 8080.
# The URL path for Pusher protocol looks like: /app/{APP_KEY}
# Check your .env file for REVERB_APP_KEY and REVERB_PORT
REVERB_APP_KEY = "xcubvd4inm14ayepjhro" # Fetched from .env
REVERB_HOST = "127.0.0.1"
REVERB_PORT = 8080
WS_URL = f"ws://{REVERB_HOST}:{REVERB_PORT}/app/{REVERB_APP_KEY}?protocol=7&client=python-iot"

# Mac Address of the IoT Device (Mocked for testing)
MAC_ADDRESS = "00:1A:2B:3C:4D:5E"
CLEAN_MAC = MAC_ADDRESS.replace(":", "")
# The channel we want to subscribe to (must match the Laravel Event channel)
CHANNEL_NAME = f"iot.stream.{CLEAN_MAC}"

async def connect_and_communicate():
    print(f"Connecting to {WS_URL}...")
    try:
        async with websockets.connect(WS_URL) as websocket:
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
            
            # Keep the main loop running, periodically sending "ping" or telemetry data
            while True:
                # Simulating sending data to the server
                # NOTE: For client-to-server WS communication in Pusher/Reverb, 
                # you typically use "client-" prefixed events on presence/private channels,
                # OR you send data via HTTP POST API, and the server broadcasts it.
                # If using HTTP POST to your /api/iot/stream endpoint, you'd use 'requests' library here instead.
                
                # To keep the connection alive
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
        print(f"Connection refused. Make sure Laravel Reverb is running on {REVERB_HOST}:{REVERB_PORT} (php artisan reverb:start)")
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
