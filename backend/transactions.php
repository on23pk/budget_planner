<?php
ini_set('display_errors', 0); // HTML-Fehlerausgabe deaktivieren
ini_set('log_errors', 1); // Fehler in die Logdatei schreiben
ini_set('error_log', __DIR__ . '/php_errors.log'); // Fehlerprotokoll
error_reporting(E_ALL); // Alle Fehler protokollieren

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nicht authentifiziert']);
    exit();
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $transactions]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, name, amount) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $data->name, $data->amount]);
    echo json_encode(['status' => 'success', 'message' => 'Transaktion hinzugefügt']);
}
?>
