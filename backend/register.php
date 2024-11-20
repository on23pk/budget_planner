<?php
ini_set('display_errors', 0); // HTML-Fehlerausgabe deaktivieren
ini_set('log_errors', 1); // Fehler in die Logdatei schreiben
ini_set('error_log', __DIR__ . '/php_errors.log'); // Fehlerprotokoll
error_reporting(E_ALL); // Alle Fehler protokollieren

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require 'db.php';

// Holen der Eingabedaten
$data = json_decode(file_get_contents("php://input"));

// Stellen sicher, dass der Benutzername und das Passwort vorhanden sind
if (isset($data->username) && isset($data->password)) {
    require 'db.php'; // Verbindung zur Datenbank

    // Sicherstellen, dass der Benutzername nicht bereits existiert
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data->username]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Benutzername bereits vergeben']);
        exit();
    }

    // Passwort hashen
    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);

    // Neuen Benutzer in die Datenbank einfügen
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt->execute([$data->username, $hashedPassword])) {
        echo json_encode(['status' => 'success', 'message' => 'Registrierung erfolgreich']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Fehler bei der Registrierung']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Eingabedaten']);
}
?>
