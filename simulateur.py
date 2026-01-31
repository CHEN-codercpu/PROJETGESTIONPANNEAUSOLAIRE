import mysql.connector
import time
import random
from datetime import datetime

# --- CONFIGURATION ---
db_config = {
    'host': '192.168.107.37',      # On attaque le localhost car Docker expose le port 3306
    'user': 'root',  # Ton utilisateur DB
    'password': 'ciel', # Ton mot de passe DB
    'database': 'db_panneau_solaire',
    'port': 3306
}

def generer_donnees():
    """Génère des fausses valeurs réalistes"""
    return {
        'tension_p': round(random.uniform(17.5, 22.0), 2),  # Entre 17.5V et 22V
        'courant_p': round(random.uniform(0.5, 5.0), 2),    # Entre 0.5A et 5A
        'tension_b': round(random.uniform(11.8, 14.4), 2),  # Batterie 12V
        'temp_b': round(random.uniform(20.0, 45.0), 1),     # Température en °C
        'lux': random.randint(200, 60000)                   # Eclairement
    }

print("☀️  Démarrage de la simulation solaire...")
print("Appuie sur CTRL+C pour arrêter.")

try:
    # Connexion à la base de données
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()
    print("✅ Connecté à la base de données !")

    while True:
        # 1. On crée les données
        data = generer_donnees()
        
        # 2. La requête SQL pour insérer
        sql = """INSERT INTO Mesures 
                 (tension_panneau, courant_panneau, tension_batterie, temp_batterie, eclairement) 
                 VALUES (%s, %s, %s, %s, %s)"""
        
        valeurs = (data['tension_p'], data['courant_p'], data['tension_b'], data['temp_b'], data['lux'])
        
        # 3. Exécution
        cursor.execute(sql, valeurs)
        conn.commit() # Important pour valider l'enregistrement
        
        timestamp = datetime.now().strftime("%H:%M:%S")
        print(f"[{timestamp}] Données envoyées : {data['tension_p']}V, {data['lux']} lux...")
        
        # 4. On attend 5 secondes avant la prochaine mesure
        time.sleep(5)

except mysql.connector.Error as err:
    print(f"❌ Erreur de connexion : {err}")
except KeyboardInterrupt:
    print("\n🛑 Arrêt de la simulation.")
finally:
    if 'conn' in locals() and conn.is_connected():
        cursor.close()
        conn.close()
        print("Connexion fermée.")
