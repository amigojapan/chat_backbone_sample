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

create table called gameswith the follwing fields:
Server_URL_ID
Client_URL_ID
server_ping
client_ping
server_nick
clint_nick
*/
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
//curl -X POST http://localhost/chatbackbone/sender.php -H "Content-Type: application/x-www-form-urlencoded" -d "receiver=amigojapan&message=hello_world"
$db = new SQLite3('/var/www/html/chatbackbone/messages.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];
    $from = $_POST['from'];

    $stmt = $db->prepare('INSERT INTO messages ("from", receiver, message) VALUES (:from, :receiver, :message)');
    //$stmt->bindValue(':sender', 'Sender', SQLITE3_TEXT);
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
