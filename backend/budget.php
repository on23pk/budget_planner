<?php
// Fehleranzeige für Debugging aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

// HTTP-Header setzen
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE"); // Erlaubte HTTP-Methoden
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Erlaubte Header
header("Access-Control-Allow-Credentials: true"); // Erlaubt Cookies und Sitzungsdaten
header("Content-Type: application/json"); // Antwortformat ist JSON

// CORS-Preflight-Anfrage behandeln (OPTIONS wird für CORS von Browsern gesendet)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
} // Antwortet mit einem leeren Ergebnis und beendet das Skript

// Datenbankverbindung einbinden
require 'db.php';

// Sitzung starten, falls nicht bereits gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log für die Sitzung
error_log("Session ID in budget.php: " . session_id());
error_log("Session-Daten: " . print_r($_SESSION, true));

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    error_log("Benutzer nicht eingeloggt.");
    echo json_encode(['status' => 'error', 'message' => 'Benutzer nicht eingeloggt']);
    exit();
}

// Aktuelle HTTP-Methode erfassen
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET': // Daten abrufen
            if (isset($_GET['id'])) {
                // Einzelne Transaktion basierend auf ID abrufen
                $stmt = $pdo->prepare("SELECT id, name, amount, category, transaction_date FROM transactions WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($transaction) {
                    $response = ['status' => 'success', 'data' => $transaction];
                } else {
                    $response = ['status' => 'error', 'message' => 'Transaktion nicht gefunden'];
                }
            } else {
                // Alle Transaktionen für den Benutzer abrufen
                $stmt = $pdo->prepare("SELECT id, name, amount, category, transaction_date FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Einnahmen und Ausgaben berechnen
                $income = 0; // Gesamteinnahmen
                $expenses = 0; // Gesamtausgaben
                foreach ($transactions as &$transaction) {
                    if ($transaction['category'] === 'Einnahme') {
                        $income += floatval($transaction['amount']);
                    } elseif ($transaction['category'] === 'Ausgabe') {
                        $expenses += floatval($transaction['amount']);
                    }
                }
                $balance = $income - $expenses; // Aktuelles Guthaben berechnen
                
                // Antwort mit Transaktionen und Berechnungen
                $response = [
                    'status' => 'success',
                    'data' => $transactions,
                    'balance' => $balance
                ];
            }
            break;

        case 'POST': // Neue Transaktion erstellen
            $data = json_decode(file_get_contents("php://input"), true); // Neue Transaktion erstellen

            // Überprüfen, ob alle benötigten Felder gesetzt sind
            if (isset($data['name'], $data['amount'], $data['category'], $data['transaction_date'])) {
                $stmt = $pdo->prepare("INSERT INTO transactions (name, amount, category, transaction_date, user_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['name'],
                    $data['amount'],
                    $data['category'],
                    $data['transaction_date'],
                    $_SESSION['user_id']
                ]);
                $response = ['status' => 'success', 'message' => 'Transaktion erfolgreich hinzugefügt'];
            } else {
                $response = ['status' => 'error', 'message' => 'Ungültige Eingabedaten'];
            }
            break;

        case 'PUT': // Bestehende Transaktion aktualisieren
            $data = json_decode(file_get_contents("php://input"), true); 

            if (isset($data['id'], $data['name'], $data['amount'], $data['category'], $data['transaction_date'])) {
                $stmt = $pdo->prepare("UPDATE transactions SET name = ?, amount = ?, category = ?, transaction_date = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([
                    $data['name'],
                    $data['amount'],
                    $data['category'],
                    $data['transaction_date'],
                    $data['id'],
                    $_SESSION['user_id']
                ]);
                $response = ['status' => 'success', 'message' => 'Transaktion erfolgreich aktualisiert'];
            } else {
                $response = ['status' => 'error', 'message' => 'Ungültige Eingabedaten'];
            }
            break;

        case 'DELETE': // Transaktion löschen
            $data = json_decode(file_get_contents("php://input"), true);

            // Überprüfen, ob die ID der Transaktion angegeben ist
            if (isset($data['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
                $stmt->execute([$data['id'], $_SESSION['user_id']]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($transaction) {
                    // Transaktion löschen, wenn sie gefunden wurde
                    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
                    $stmt->execute([$data['id'], $_SESSION['user_id']]);
                    $response = ['status' => 'success', 'message' => 'Transaktion gelöscht'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Transaktion nicht gefunden oder gehört nicht zum Benutzer'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Fehlende Transaktions-ID'];
            }
            break;

        default: // Unbekannte Methode
            $response = ['status' => 'error', 'message' => 'Unbekannte Methode'];
            break;
    }
} catch (Exception $e) {
    // Fehlerbehandlung: Fehler ins Log schreiben und eine Fehlermeldung zurückgeben
    error_log("Fehler in der API: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// JSON-Antwort an den Client zurückgeben
echo json_encode($response);
?>
