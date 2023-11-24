<?php
// Enable error reporting
//http://localhost/chatbackbone/receiver.php?receiver=amigojapan
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new SQLite3('/var/www/html/chatbackbone/messages.db');

$receiver = $_GET['receiver'];

$stmt = $db->prepare('SELECT * FROM messages WHERE receiver = :receiver ORDER BY created_at DESC LIMIT 1');
$stmt->bindValue(':receiver', $receiver, SQLITE3_TEXT);
$result = $stmt->execute();
$messageData = $result->fetchArray(SQLITE3_ASSOC);

if ($messageData) {
    echo "Sender: " . $messageData['from'] . "\n";
    echo "Message: " . $messageData['message'] . "\n";
    // You can add additional processing here if needed

    // Optional: Delete the received message from the database
    $db->exec('DELETE FROM messages WHERE id = ' . $messageData['id']);
} else {
    echo "No messages.";
}
?>
