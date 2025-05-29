#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

// **Deklarasi Pin**
#define PIN_TOMBOL 14  // GPIO14 (D5) untuk sensor sentuh TTP233B
#define PIN_BUZZER 12  // GPIO12 (D6) untuk buzzer
#define LAMPU_KUNING 13  // GPIO13 (D7) untuk lampu kuning
#define LAMPU_HIJAU 15   // GPIO15 (D8) untuk lampu hijau

// **Konfigurasi Wi-Fi**
const char* ssid = "S23 5G";  // Nama Wi-Fi
const char* password = "samsunggalaxys23";  // Kata sandi Wi-Fi
const char* server = "http://192.168.191.59/skripsian/queue.php"; // URL server

// **Variabel kontrol**
bool tombolProses = false;
unsigned long lastTime = 0;
const unsigned long relayPush = 7000; // Jeda antar pencetan tombol (7 detik)

void setup() {
    Serial.begin(115200);
    pinMode(PIN_TOMBOL, INPUT_PULLUP);
    pinMode(PIN_BUZZER, OUTPUT);
    pinMode(LAMPU_KUNING, OUTPUT);
    pinMode(LAMPU_HIJAU, OUTPUT);

    digitalWrite(PIN_BUZZER, LOW);
    digitalWrite(LAMPU_KUNING, LOW);
    digitalWrite(LAMPU_HIJAU, HIGH); // Lampu hijau menyala di awal

    // **Menghubungkan ke Wi-Fi**
    WiFi.begin(ssid, password);
    Serial.print("Menghubungkan ke Wi-Fi...");
    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        Serial.print(".");
    }
    Serial.println("\nWi-Fi Berhasil Terhubung!");

    delay(2000);  // Delay untuk stabilisasi koneksi
    while (digitalRead(PIN_TOMBOL) == LOW) {
        delay(500);  // Tunggu hingga tombol benar-benar tidak ditekan saat awal nyala
    }

    tombolProses = false;  // Pastikan sistem tidak langsung mengirim nomor antrean
}

void loop() {
    // **Jika koneksi Wi-Fi terputus, coba sambungkan ulang**
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("Koneksi Wi-Fi terputus! Mencoba menyambungkan kembali...");
        WiFi.begin(ssid, password);
        delay(5000);
        return;
    }

    static int statusTombolTerakhir = HIGH;
    int statusTombol = digitalRead(PIN_TOMBOL);
    unsigned long waktuSaatIni = millis();

    // **Cek apakah tombol ditekan dan belum diproses**
    if (!tombolProses && statusTombol == LOW && statusTombolTerakhir == HIGH && (waktuSaatIni - lastTime >= relayPush)) { 
        Serial.println("Tombol ditekan! Mengirim nomor antrean...");

        // **Bunyikan buzzer**
        digitalWrite(PIN_BUZZER, HIGH);
        delay(700);
        digitalWrite(PIN_BUZZER, LOW);

        kirimNomorAntrean();  // Kirim data ke server

        // **Efek lampu indikator**
        digitalWrite(LAMPU_HIJAU, LOW);
        digitalWrite(LAMPU_KUNING, HIGH);
        delay(5000);
        digitalWrite(LAMPU_KUNING, LOW);
        digitalWrite(LAMPU_HIJAU, HIGH);

        lastTime = waktuSaatIni;
        tombolProses = true;  // Pastikan tombol hanya diproses sekali saat ditekan
    }

    // **Reset flag ketika tombol dilepas**
    if (statusTombol == HIGH) {
        tombolProses = false;
    }

    statusTombolTerakhir = statusTombol;
    delay(100);
}

// **Fungsi untuk mengirim nomor antrean ke server**
void kirimNomorAntrean() {
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient client;
        HTTPClient http;

        http.begin(client, server);
        int kodeRespon = http.GET();

        Serial.print("Kode Respon: ");
        Serial.println(kodeRespon);
        if (kodeRespon > 0) {
            Serial.println("Nomor antrean berhasil dikirim!");
        } else {
            Serial.println("Gagal menghubungi server!");
        }
        
        http.end();
    }
}
