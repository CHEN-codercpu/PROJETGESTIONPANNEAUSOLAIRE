import paho.mqtt.client as mqtt
import json
import MySQLdb
import time
import sys
import warnings
 
warnings.filterwarnings("ignore", category=DeprecationWarning)
 
# --- CONFIGURATION ---
TTN_BROKER = "eu1.cloud.thethings.network"
TTN_USERNAME = "panneau-solaire-54@ttn"
TTN_PASSWORD = "NNSXS.TCNSNLH2UB4GNCTKG2LMVFJOOTWPSQD34JUZE7A.HH5TNUYQXVL5RQL2D5YP5NNX4Z4HE2LSJMQLIB2PU2UIZ4237EWA"
 
LOCAL_BROKER = "127.0.0.1"
# TOPIC EXACT utilisé par Lucas dans son code C++
TOPIC_LUCAS_WIFI = "ecole/groupe54/solaire"
 
DB_HOST = "127.0.0.1"
DB_USER = "root"
DB_PASS = "ciel"
DB_NAME = "db_panneau_solaire"
 
# --- FONCTION D'INSERTION EN BASE ---
def sauvegarder_bdd(v_pan, i_pan, v_bat, temp, lux):
    try:
        db = MySQLdb.connect(host=DB_HOST, user=DB_USER, passwd=DB_PASS, db=DB_NAME)
        cursor = db.cursor()
        sql = """INSERT INTO Mesures 
                 (tension_panneau, courant_panneau, tension_batterie, temp_batterie, eclairement, date_heure) 
                 VALUES (%s, %s, %s, %s, %s, NOW())"""
        cursor.execute(sql, (v_pan, i_pan, v_bat, temp, lux))
        db.commit()
        db.close()
        print(f"✅ BDD mise à jour : Panneau={v_pan}V | Batt={v_bat}V")
    except Exception as e:
        print(f"❌ Erreur BDD : {e}")
 
# --- CALLBACK 1 : DONNÉES WIFI (LUCAS EN DIRECT) ---
def on_message_local(client, userdata, msg):
    try:
        payload = json.loads(msg.payload.decode("utf-8"))
        v_pan = payload.get('tension_p', 0)
        i_pan = payload.get('courant_p', 0)
        print(f"📡 WiFi reçu -> Panneau: {v_pan}V")
        # On sauvegarde (en mettant 0 pour les valeurs LoRa qu'on n'a pas encore)
        sauvegarder_bdd(v_pan, i_pan, 0, 0, 0)
    except Exception as e:
        print(f"Erreur Local MQTT : {e}")
 
# --- CALLBACK 2 : DONNÉES CLOUD (LORAWAN) ---
def on_message_ttn(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode("utf-8"))
        payload = data.get('uplink_message', {}).get('decoded_payload', {})
        v_bat = payload.get('tension_b', 0)
        temp = payload.get('temp', 0)
        print(f"☁️ LoRa reçu -> Batterie: {v_bat}V")
        # On sauvegarde (en mettant 0 pour les valeurs WiFi)
        sauvegarder_bdd(0, 0, v_bat, temp, 0)
    except Exception as e:
        print(f"Erreur TTN MQTT : {e}")
 
# --- INITIALISATION ---
 
# Client pour Lucas (WiFi Local)
client_local = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client_local.on_message = on_message_local
 
# Client pour TTN (LoRaWAN)
client_ttn = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client_ttn.username_pw_set(TTN_USERNAME, TTN_PASSWORD)
client_ttn.on_message = on_message_ttn
 
try:
    print("🚀 Lancement du Pont Multi-Réseaux...")
    # Connexion au broker de la Pi
    client_local.connect(LOCAL_BROKER, 1883)
    client_local.subscribe(TOPIC_LUCAS_WIFI)
    client_local.loop_start() # On lance en arrière-plan
    # Connexion à TTN
    client_ttn.connect(TTN_BROKER, 1883)
    client_ttn.subscribe("v3/+/devices/+/up")
    print("✅ Système en ligne. Écoute WiFi et LoRa activée.")
    client_ttn.loop_forever() # On bloque sur TTN
 
except KeyboardInterrupt:
    print("\nArrêt.")
    sys.exit()
