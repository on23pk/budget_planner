<?php
// Aktiviert die Anzeige von Fehlern für Debugging-Zwecke
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Setzt die notwendigen HTTP-Header für CORS und JSON-Antworten
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Startet die Sitzung, falls sie noch nicht gestartet wurde
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Beendet die Sitzung
session_unset();
session_destroy();

// Antwort zurückgeben
echo json_encode(['status' => 'success', 'message' => 'Logout erfolgreich']);
?>