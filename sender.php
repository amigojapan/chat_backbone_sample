<?php
/*
PRAGMA table_info(messages);
0|id|INTEGER|0||1
1|sender|TEXT|0||0
2|receiver|TEXT|0||0
3|message|TEXT|0||0
4|created_at|TIMESTAMP|0|CURRENT_TIMESTAMP|0
5|from|TEXT|0||0 
6|server_ping|INTEGER|0||0 more this field to games
7|server_url|TEXT|0||0 (rename ot URL_ID)
8|client_ping|INTEGER|0||0 more this field to games

CREATE TABLE IF NOT EXISTS games (
    Server_URL_ID TEXT,
    Client_URL_ID TEXT,
    server_ping TEXT,
    client_ping TEXT,
    server_nick TEXT,
    client_nick TEXT
);
*/
// Enable error reporting
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);
//curl -X POST http://localhost/chatbackbone/sender.php -H "Content-Type: application/x-www-form-urlencoded" -d "receiver=amigojapan&message=hello_world"
$db = new SQLite3('/var/www/html/chatbackbone/messages.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];
    $from = $_POST['from'];
    $Server_URL_ID = $_GET['URL'];
    $host_or_client = $_GET['host_or_client'];
    echo "Server_URL_ID:".$Server_URL_ID;

    if($host_or_client=="host"){
        $stmt = $db->prepare('INSERT INTO games (Server_URL_ID,server_nick) VALUES (:Server_URL_ID, :server_nick)');
        //$stmt->bindValue(':sender', 'Sender', SQLITE3_TEXT);
        $stmt->bindValue(':Server_URL_ID', $Server_URL_ID, SQLITE3_TEXT);
        $stmt->bindValue(':server_nick', $from, SQLITE3_TEXT);
        $stmt->execute();
    }

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

    $stmt = $db->prepare('INSERT INTO messages (server_url,"from", receiver, message) VALUES (:server_url, :from, :receiver, :message)');
    //$stmt->bindValue(':sender', 'Sender', SQLITE3_TEXT);
    $stmt->bindValue(':server_url', $Server_URL_ID, SQLITE3_TEXT);
    $stmt->bindValue(':from', $from, SQLITE3_TEXT);
    $stmt->bindValue(':receiver', $receiver, SQLITE3_TEXT);
    $stmt->bindValue(':message', $message, SQLITE3_TEXT);
    $stmt->execute();
    echo "Message sent successfully.";

    } else {
        echo "Invalid request method.";
    }
    echo "Session is alive!";//used as keep alive message
?>