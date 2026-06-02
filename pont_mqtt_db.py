import paho.mqtt.client as mqtt
import json
import MySQLdb
import sys
import warnings

warnings.filterwarnings("ignore", category=DeprecationWarning)

# --- CONFIGURATION ---
TTN_BROKER = "eu1.cloud.thethings.network"
TTN_USERNAME = "panneau-solaire-54@ttn"
TTN_PASSWORD = "NNSXS.XKZKEVHAOC3PH7TTM57H22YSC6AQXL62AV2EP3Y.AWWRFYRR5E5GJAXCRGSAZMUQINFHCKH4YZK5EZEFF4TB2ZLOC4VA"

LOCAL_BROKER = "127.0.0.1"
TOPIC_LUCAS_WIFI = "ecole/groupe54/solaire"

DB_HOST = "127.0.0.1"
DB_USER = "root"
DB_PASS = "ciel"
DB_NAME = "db_panneau_solaire"

# --- FONCTION D'INSERTION ---
def sauvegarder_bdd(v_pan, i_pan, v_bat, temp, lux):
    try:
        db = MySQLdb.connect(host=DB_HOST, user=DB_USER, passwd=DB_PASS, db=DB_NAME)
        cursor = db.cursor()
        cursor.execute("SET time_zone = '+02:00';")
        
        # Note : On garde la structure de ta table Mesures
        sql = """INSERT INTO Mesures
                 (tension_panneau, courant_panneau, tension_batterie, temp_batterie, eclairement, date_heure)
                 VALUES (%s, %s, %s, %s, %s, NOW())"""

        cursor.execute(sql, (v_pan, i_pan, v_bat, temp, lux))
        db.commit()
        db.close()
        print(f"Archivé : Batt={v_bat}V | Pan={i_pan}mA | Temp={temp}°C | Lum={lux}")
    except Exception as e:
        print(f"Erreur BDD : {e}")

# --- CALLBACK WIFI (LUCAS) ---
def on_message_local(client, userdata, msg):
    try:
        payload = json.loads(msg.payload.decode("utf-8"))
        
        # Lucas utilise maintenant ces noms précis dans son code C++ :
        v_bat = payload.get('tension_b', 0)
        i_pan = payload.get('courant_p', 0)
        temp  = payload.get('temperature', 0)
        lux   = payload.get('luminosite', 0)

        print(f"📡 WiFi -> Batterie: {v_bat}V | Courant: {i_pan}mA")
        # On enregistre (v_pan est mis à 0 car Lucas ne l'envoie plus séparément en WiFi)
        sauvegarder_bdd(0, i_pan, v_bat, temp, lux)
    except Exception as e:
        print(f"rreur WiFi : {e}")

# --- CALLBACK LORA (TTN) ---
def on_message_ttn(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode("utf-8"))
        p = data.get('uplink_message', {}).get('decoded_payload', {})

        # Récupération des données décodées par le Payload Formatter JS
        v_bat = p.get('tension_b', 0)
        i_pan = p.get('courant_p', 0)
        temp  = p.get('temperature', 0)
        lux   = p.get('luminosite', 0)

        print(f"☁️ LoRa -> Batterie: {v_bat}V | Courant: {i_pan}mA")
        sauvegarder_bdd(0, i_pan, v_bat, temp, lux)
    except Exception as e:
        print(f"rreur TTN : {e}")

# --- INITIALISATION ---
client_local = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client_local.on_message = on_message_local

client_ttn = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client_ttn.username_pw_set(TTN_USERNAME, TTN_PASSWORD)
client_ttn.on_message = on_message_ttn

try:
    client_local.connect(LOCAL_BROKER, 1883)
    client_local.subscribe(TOPIC_LUCAS_WIFI)
    client_local.loop_start()

    client_ttn.connect(TTN_BROKER, 1883)
    client_ttn.subscribe("v3/+/devices/+/up")

    print("Pont prêt : Écoute WiFi et LoRa (8 octets)...")
    client_ttn.loop_forever()

except KeyboardInterrupt:
    sys.exit()
