import paho.mqtt.client as mqtt
import json
import time

# --- CONFIGURATION (La même que ton pont) ---
TTN_BROKER = "eu1.cloud.thethings.network"
TTN_USERNAME = "panneau-solaire-54@ttn"
TTN_PASSWORD = "NNSXS.L6GZIZDNWKAZVOQWDEIU3KAVI3MTRBVYIL5XGRY.LOZ5L7CMPW2NUT5RY6ARRTJQO3LIZ46E6SW3EXOPJ4EVO5QH2Q5Q"
# Ici, on vise un appareil précis (ex: 'esp32-test')
TOPIC_ENVOI = "v3/panneau-solaire-54@ttn/devices/esp32-test/up"

client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.username_pw_set(TTN_USERNAME, TTN_PASSWORD)
client.connect(TTN_BROKER, 1883, 60)

# On simule le message que TTN enverrait normalement
payload = {
    "uplink_message": {
        "decoded_payload": {
            "tension": 12.8,
            "courant": 1.5
        }
    }
}

print("🚀 Envoi d'une fausse donnée solaire...")
client.publish(TOPIC_ENVOI, json.dumps(payload))
client.disconnect()
print("✅ Message envoyé ! Vérifie ton pont.")
