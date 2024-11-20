<?php
// Fehleranzeige für Debugging aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

// HTTP-Header setzen
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// CORS-Preflight-Anfrage behandeln
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

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
        case 'GET':
            if (isset($_GET['id'])) {
                // Einzelne Transaktion abrufen
                $stmt = $pdo->prepare("SELECT id, name, amount, category, transaction_date FROM transactions WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($transaction) {
                    $response = ['status' => 'success', 'data' => $transaction];
                } else {
                    $response = ['status' => 'error', 'message' => 'Transaktion nicht gefunden'];
                }
            } else {
                // Alle Transaktionen abrufen
                $stmt = $pdo->prepare("SELECT id, name, amount, category, transaction_date FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $income = 0;
                $expenses = 0;
                foreach ($transactions as &$transaction) {
                    if ($transaction['category'] === 'Einnahme') {
                        $income += floatval($transaction['amount']);
                    } elseif ($transaction['category'] === 'Ausgabe') {
                        $expenses += floatval($transaction['amount']);
                    }
                }
                $balance = $income - $expenses;

                $response = [
                    'status' => 'success',
                    'data' => $transactions,
                    'balance' => $balance
                ];
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);

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

        case 'PUT':
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

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"), true);

            if (isset($data['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
                $stmt->execute([$data['id'], $_SESSION['user_id']]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($transaction) {
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

        default:
            $response = ['status' => 'error', 'message' => 'Unbekannte Methode'];
            break;
    }
} catch (Exception $e) {
    error_log("Fehler in der API: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
?>
