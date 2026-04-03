import paho.mqtt.client as mqtt
import json
import MySQLdb
import sys
import warnings

# On ignore les avertissements de version pour un log propre
warnings.filterwarnings("ignore", category=DeprecationWarning)

# --- CONFIGURATION TTN ---
TTN_BROKER = "eu1.cloud.thethings.network:1883"
TTN_USERNAME = "panneau-solaire-54@ttn"
TTN_PASSWORD = "NNSXS.2JPGD6NYK2AZR7TXQQLZ6XWVL2RPAWIGG5CKGAI.D7UIGLLNSA4HFG5DSOSUH7UBFNXCQ3CV5KRO35ZFTLK2EM44BWHA"
TTN_TOPIC = "V3/panneau-solaire-54@ttn/devices/+/up"

# --- CONFIGURATION BDD (127.0.0.1 pour Docker) ---
DB_HOST = "127.0.0.1"
DB_USER = "root"
DB_PASS = "ciel"
DB_NAME = "db_panneau_solaire"

def on_connect(client, userdata, flags, rc, properties=None):
    if rc == 0:
        print("Connexion établie avec le Cloud TTN. Écoute active...")
        client.subscribe(TTN_TOPIC)
    else:
        print(f"Erreur de connexion au Broker. Code : {rc}")

def on_message(client, userdata, msg):
    try:
        # 1. Décodage du message JSON
        data = json.loads(msg.payload.decode("utf-8"))
        
	# 2. EXTRACTION DES DONNÉES RÉELLES (Contrat d'interface avec l'ESP32)
        val_temp = data.get('temp', 0)      # On chrche la clé 'temp' dans le JSON
        val_lux  = data.get('lux', 0)       # On cherche la clé 'lux'
        val_tension_p = data.get('tension_p', 0)
        val_courant_p = data.get('courant_p', 0)
        val_tension_b = data.get('tension_b', 0)

        print(f"Reçu : Temp={val_temp}°C | Lux={val_lux} | (Simu: {val_tension_p}V)")

        # 4. CONNEXION ET INSERTION DANS MARIADB
        db = MySQLdb.connect(host=DB_HOST, user=DB_USER, passwd=DB_PASS, db=DB_NAME)
        cursor = db.cursor()
        
        # Requête SQL pour remplir les 5 colonnes d'un coup
        sql = """INSERT INTO Mesures 
                 (tension_panneau, courant_panneau, tension_batterie, temp_batterie, eclairement, date_heure) 
                 VALUES (%s, %s, %s, %s, %s, NOW())"""
        
        cursor.execute(sql, (val_tension_p, val_courant_p, val_tension_b, val_temp, val_lux))
        
        db.commit()
        db.close()
        print("Toutes les cases ont été mises à jour en base de données.")

    except Exception as e:
        print(f"rreur lors du traitement : {e}")

# --- INITIALISATION DU CLIENT ---
client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.username_pw_set(TTN_USERNAME, TTN_PASSWORD)
client.on_connect = on_connect
client.on_message = on_message

try:
    print("Lancement du pont de données...")
    client.connect(TTN_BROKER, 1883, 60)
    client.loop_forever()
except KeyboardInterrupt:
    print("\n Arrêt du script.")
    sys.exit()
