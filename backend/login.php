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
    'domain' => 'localhost',  
    'secure' => false,   
    'httponly' => true,  // Verhindert den Zugriff auf das Cookie durch JavaScript
    'samesite' => 'Lax', // SameSite-Policy
]);

session_start();  // Session nach den oben definierten Parametern starten

require 'db.php';

$data = json_decode(file_get_contents("php://input"));

// Überprüfen, ob sowohl Benutzername als auch Passwort übergeben wurden
if (isset($data->username) && isset($data->password)) {
    // SQL-Statement vorbereiten, um Benutzer in der Datenbank zu suchen
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data->username]); // Benutzername aus den Eingabedaten verwenden
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Benutzer aus der Datenbank abrufen

    // Überprüfen, ob der Benutzer existiert und das Passwort korrekt ist
    if ($user && password_verify($data->password, $user['password'])) {
        // Session starten und Benutzer-ID speichern
        $_SESSION['user_id'] = $user['id']; // Benutzer-ID in der Session speichern

        // Erfolgreiche Antwort im JSON-Format
        echo json_encode([
            'status' => 'success', // Erfolgreiche Anmeldung
            'message' => 'Login erfolgreich', // Bestätigung der Anmeldung
            'user_id' => $_SESSION['user_id'] // Benutzer-ID direkt in der Antwort zurückgeben
        ]);
    } else {
        // Wenn Benutzername oder Passwort falsch sind, eine Fehlermeldung zurückgeben
        echo json_encode(['status' => 'error', 'message' => 'Falscher Benutzername oder Passwort']);
    }
} else {
    // Wenn die Eingabedaten unvollständig sind, eine Fehlermeldung zurückgeben
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Eingabedaten']);
}
?>
