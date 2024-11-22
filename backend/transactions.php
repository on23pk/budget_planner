<?php
ini_set('display_errors', 0); // HTML-Fehlerausgabe deaktivieren
ini_set('log_errors', 1); // Fehler in die Logdatei schreiben
ini_set('error_log', __DIR__ . '/php_errors.log'); // Fehlerprotokoll
error_reporting(E_ALL); // Alle Fehler protokollieren

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// Sitzung starten, um die Benutzeridentität zu verwalten
session_start();

// Überprüfen, ob der Benutzer authentifiziert ist
if (!isset($_SESSION['user_id'])) {
    // Wenn der Benutzer nicht eingeloggt ist, wird eine Fehlermeldung zurückgegeben
    echo json_encode(['status' => 'error', 'message' => 'Nicht authentifiziert']);
    exit(); // Skript abbrechen
}

require 'db.php'; // Datenbankverbindung einbinden

// Überprüfen der HTTP-Methode
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verarbeitung einer GET-Anfrage: Abrufen der Transaktionen des eingeloggten Benutzers
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ?"); // SQL-Statement vorbereiten
    $stmt->execute([$_SESSION['user_id']]); // Benutzer-ID aus der Session verwenden
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC); // Alle Transaktionen abrufen

    // JSON-Antwort mit den Transaktionen senden
    echo json_encode(['status' => 'success', 'data' => $transactions]); // Alle Transaktionen abrufen
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verarbeitung einer POST-Anfrage: Hinzufügen einer neuen Transaktion
    $data = json_decode(file_get_contents("php://input")); // JSON-Daten aus dem Request-Body lesen

    // SQL-Statement vorbereiten und neue Transaktion einfügen
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, name, amount) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $data->name, $data->amount]); // Werte einfügen (Name und Betrag)

    // Erfolgsmeldung als JSON zurückgeben
    echo json_encode(['status' => 'success', 'message' => 'Transaktion hinzugefügt']);
}
?>
