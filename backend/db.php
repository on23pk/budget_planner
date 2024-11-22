<?php
// Starten der Session, wenn noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurationsvariablen für die Datenbankverbindung
$host = 'localhost';
$dbname = 'budgetplaner';
$username = 'root';  // Ersetzen mit eigenem DB-Benutzernamen
$password = '';      // Ersetzen mit eigenem DB-Passwort

// Fehlerprotokollierung für Debugging
error_log("Versuche, die Datenbankverbindung herzustellen...");

// Überprüfung, ob die Session korrekt gestartet wurde und die Benutzer-ID gesetzt ist
error_log("Session ID: " . session_id());
if (!isset($_SESSION['user_id'])) {
    error_log("Benutzer nicht eingeloggt, keine Benutzer-ID in der Sitzung.");
} else {
    error_log("Benutzer-ID: " . $_SESSION['user_id']);
}

try {
    // Aufbau der PDO-Verbindung zur Datenbank
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Setzen der Attribute für das Fehlerhandling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Bestätigungslog, wenn die Verbindung erfolgreich war
    error_log("Datenbankverbindung erfolgreich!");

} catch (PDOException $e) {
    // Fehlerprotokollierung im Fehlerfall
    error_log("Fehler bei der Datenbankverbindung: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Fehler bei der Datenbankverbindung']);
    exit(); // Skript stoppen, wenn keine Verbindung zur DB hergestellt werden kann
}

// Loggen des Speicherorts der Sessions
error_log("Session gespeichert unter: " . session_save_path());
?>


