#include <Wire.h>
#include <BH1750.h>
#include <OneWire.h>
#include <DallasTemperature.h>

// --- Configuration des broches ---
const int oneWireBus = 17;     // DS18B20 déplacé sur le port P17
#define I2C_SDA 21            // BH1750 SDA sur P21
#define I2C_SCL 22            // BH1750 SCL sur P22

// --- Initialisation des capteurs ---
OneWire oneWire(oneWireBus);
DallasTemperature sensors(&oneWire);
BH1750 lightMeter;

void setup() {
  Serial.begin(115200);
  
  // Démarrage de la température sur le nouveau port
  sensors.begin();
  Serial.println("DS18B20 initialisé sur le port 17.");

  // Démarrage de la luminosité (I2C)
  Wire.begin(I2C_SDA, I2C_SCL);
  if (lightMeter.begin(BH1750::CONTINUOUS_HIGH_RES_MODE)) {
    Serial.println("BH1750 initialisé.");
  } else {
    Serial.println("Erreur BH1750.");
  }
}

void loop() {
  sensors.requestTemperatures(); 
  float tempC = sensors.getTempCByIndex(0);
  float lux = lightMeter.readLightLevel();

  Serial.print("Temp: ");
  if(tempC == DEVICE_DISCONNECTED_C) {
    Serial.print("ERREUR");
  } else {
    Serial.print(tempC);
    Serial.print("°C");
  }

  Serial.print(" | Lum: ");
  Serial.print(lux);
  Serial.println(" lx");

  delay(2000); 
}