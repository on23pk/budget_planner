<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Session-Konfiguration (sicherstellen, dass Cookie-Parameter richtig gesetzt werden)
session_set_cookie_params([
    'lifetime' => 3600,  // Session läuft nach einer Stunde ab
    'path' => '/',       // Cookie ist für die gesamte Domain gültig
    'domain' => 'localhost',  // Bei Bedarf anpassen
    'secure' => false,   // Falls du HTTPS verwendest, setze es auf true
    'httponly' => true,  // Verhindert den Zugriff auf das Cookie durch JavaScript
    'samesite' => 'Lax', // SameSite-Policy
]);

session_start();  // Session nach den oben definierten Parametern starten

require 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->username) && isset($data->password)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data->username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data->password, $user['password'])) {
        // Session starten und Benutzer-ID speichern
        $_SESSION['user_id'] = $user['id']; // Benutzer-ID in der Session speichern

        // Erfolgreiche Antwort im JSON-Format
        echo json_encode([
            'status' => 'success',
            'message' => 'Login erfolgreich',
            'user_id' => $_SESSION['user_id'] // Benutzer-ID direkt in der Antwort zurückgeben
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Falscher Benutzername oder Passwort']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Eingabedaten']);
}
?>
