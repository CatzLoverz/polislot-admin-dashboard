import requests
import time
import base64

# Configuration
API_URL = "http://127.0.0.1:8000/api/iot/stream"
MAC_ADDRESS = "00:1A:2B:3C:4D:5E"

def send_telemetry():
    """
    Simulates sending text/telemetry data to the Laravel server via HTTP POST.
    The Laravel server will then broadcast this data via WebSocket to the dashboard.
    """
    print(f"Starting telemetry sender to {API_URL}...")
    
    counter = 1
    while True:
        # For now, we simulate text data. Later this could be base64 image strings.
        simulated_data = f"Hello from IoT Device! Message number: {counter}"
        
        payload = {
            "mac_address": MAC_ADDRESS,
            "frame": simulated_data
        }
        
        try:
            print(f"Sending data to server: {simulated_data}")
            response = requests.post(API_URL, json=payload)
            
            if response.status_code == 200:
                print(f"Success: {response.json()}")
            else:
                print(f"Failed with status {response.status_code}: {response.text}")
                
        except requests.exceptions.RequestException as e:
            print(f"Error connecting to API: {e}")
            
        counter += 1
        time.sleep(3) # Send every 3 seconds

if __name__ == "__main__":
    send_telemetry()
