<?php
// Enable error reporting
//http://localhost/chatbackbone/receiver.php?receiver=amigojapan
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new SQLite3('/var/www/html/chatbackbone/messages.db');

$Server_URL_ID = $_GET['URL'];
$host_or_client = $_GET['host_or_client'];
//$receiver = $_GET['receiver'];

if($host_or_client=="host"){
    $stmt = $db->prepare('INSERT INTO games (Server_URL_ID,server_nick) VALUES (:Server_URL_ID, :server_nick)');
    //$stmt->bindValue(':sender', 'Sender', SQLITE3_TEXT);
    $stmt->bindValue(':Server_URL_ID', $Server_URL_ID, SQLITE3_TEXT);
    $stmt->bindValue(':server_nick', $from, SQLITE3_TEXT);
    $stmt->execute();
}

//update ping
$stmt = $db->prepare('INSERT INTO games (Server_URL_ID,server_nick) VALUES (:Server_URL_ID, :server_nick)');
//$stmt->bindValue(':sender', 'Sender', SQLITE3_TEXT);
$stmt->bindValue(':client_ping', time(), SQLITE3_TEXT);
$stmt->bindValue(':server_ping', time(), SQLITE3_TEXT);
$stmt->execute();

$stmt = $db->prepare('SELECT * FROM games WHERE Server_URL_ID = :Server_URL_ID ORDER BY Server_URL_ID DESC LIMIT 1');
$stmt->bindValue(':Server_URL_ID', $Server_URL_ID, SQLITE3_TEXT);
$result = $stmt->execute();
$messageData = $result->fetchArray(SQLITE3_ASSOC);
if ($messageData) {
    if($host_or_client=="host"){
        $from = $messageData['server_nick'];
        $receiver= $messageData['client_nick'];
    } else {
        $from = $messageData['client_nick'];
        $receiver= $messageData['server_nick'];

    }
} else {
    die("error: trying to access game that does not exist");
}


if($_GET("keep_alive_check")==True) {
    $stmt = $db->prepare('SELECT * FROM messages WHERE Server_URL_ID = :Server_URL_ID ORDER BY Server_URL_ID DESC LIMIT 1');
    $stmt->bindValue(':Server_URL_ID', $Server_URL_ID, SQLITE3_TEXT);
    $result = $stmt->execute();
    $messageData = $result->fetchArray(SQLITE3_ASSOC);
    if ($messageData) {
        if($host_or_client=="host") {
            $last_ping = $messageData['client_ping'];
        } else {
            $last_ping = $messageData['server_ping'];
        }
    }
    
    echo $host_or_client . " Unix time now:" . $last_ping;
}



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
