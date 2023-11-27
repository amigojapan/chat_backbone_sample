<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <?PHP
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        session_start();
        if(!isset($_SESSION["chatongoing"])){
            if (isset($_GET["URL"])&&isset($_GET["nickname"]) );
            
            $db = new SQLite3('/var/www/html/chatbackbone/messages.db');
            
            
            $databaseFile = '/var/www/html/chatbackbone/messages.db';  // Replace with the actual path to your SQLite database file

            try {
                // Create or open the database
                $db = new SQLite3($databaseFile);

                // Server_URL_ID and server_nick values to insert
                $serverURLID = $_GET['URL'];;
                $serverNick = $_GET['nickname'];;

                // Check if the record with Server_URL_ID already exists
                $query = $db->prepare('SELECT * FROM games WHERE Server_URL_ID = :serverURLID');
                $query->bindParam(':serverURLID', $serverURLID, SQLITE3_TEXT);
                $result = $query->execute();

                // If the record doesn't exist, insert a new record
                if ($result->fetchArray(SQLITE3_ASSOC) === false) {
                    $insertQuery = $db->prepare('INSERT INTO games (Server_URL_ID, server_nick) VALUES (:serverURLID, :serverNick)');
                    $insertQuery->bindParam(':serverURLID', $serverURLID, SQLITE3_TEXT);
                    $insertQuery->bindParam(':serverNick', $serverNick, SQLITE3_TEXT);
                    $insertQuery->execute();

                    echo 'waiting for other user to join chat';
                } else {
                    //echo 'Record with Server_URL_ID already exists.';
                    $insertQuery = $db->prepare('UPDATE games SET client_nick = :serverURLID WHERE Server_URL_ID = :serverURLID');
                    $insertQuery->bindParam(':serverURLID', $serverURLID, SQLITE3_TEXT);
                    $insertQuery->bindParam(':serverNick', $serverNick, SQLITE3_TEXT);
                    $insertQuery->execute();

                    //start game
                    $_SESSION["chatongoing"] = True;
                }
                // Close the database connection
                $db->close();
                } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage();
                }

            }
        ?>
    <script>
        var keep_alive_count=0;
        window.addEventListener('load', function () {
            document.getElementById('myForm').addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent the form from submitting in the traditional way

                // Collect form data
                var formData = new FormData(this);

                // Send the data using AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'http://localhost/chatbackbone/sender.php?URL=<?php print $_GET['URL']."&host_or_client=".$_GET['host_or_client']?>', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
						document.getElementById('response').innerHTML += xhr.responseText;
                    }
                };
                xhr.send(formData);
            });
            
			function fetchDataAndUpdateDiv() {
				var xhr2 = new XMLHttpRequest();

				// Configure it: GET-request for the URL /process.php
				xhr2.open('GET', 'http://localhost/chatbackbone/receiver.php?receiver=?URL=<?php print $_GET['URL']."&host_or_client=".$_GET['host_or_client']?>', true);
				// Set up a function that will be called when the request is successfully completed
				xhr2.onload = function () {
					if (xhr2.status === 200) {
						// Update the content of the 'response' div with the received data
						if(xhr2.responseText!="No messages.") {
							document.getElementById('response').innerHTML += xhr2.responseText;
						}
					}
				};
            }
            function keep_alive() {
                var xhr = new XMLHttpRequest();
                // Configure it: POST-request for the URL /your_php_script.php
                xhr.open('GET', 'http://localhost/chatbackbone/receiver.php?receiver=?URL=<?php print $_GET['URL']."&host_or_client=".$_GET['host_or_client']."&keep_alive_check=True"?>', true);
                // Set up a function that will be called when the request is successfully completed
                xhr.onload = function () {
                    if (xhr.status === 200) { 
                        // Update the content of the 'response' div with the received data
                        var is_alive=false;
                        if (str_contains(xhr.responseText, "Unix time now:")) {//<?php //echo 'Unix time now:'. time();?>
                            is_alive=true;
                            if(str.split('now:')[1]-Date.now()>20) {//str.split('now:')[1] gets everything to the right of now;
                                is_alive=false;
                            }
                        }

                        if(!is_alive) {
                            keep_alive_count++;
                            if(keep_alive_count==5) {
                                alert("chat session ended by timeout of disconnection, keep_alive_count:"+keep_alive_count)
                            }
                        }
                        document.getElementById('response').innerHTML += xhr.responseText;
                    }
                };

            
				var xhr2 = new XMLHttpRequest();

				// Configure it: GET-request for the URL /process.php
				xhr2.open('GET', 'http://localhost/chatbackbone/receiver.php?receiver=?URL=<?php print $_GET['URL']."&host_or_client=".$_GET['host_or_client']."&keep_alive_check=True"?>', true);
				// Set up a function that will be called when the request is successfully completed
				xhr2.onload = function () {
					if (xhr2.status === 200) {
						// Update the content of the 'response' div with the received data
						if(xhr2.responseText!="No messages.") {
							document.getElementById('response').innerHTML += xhr2.responseText;
						}
					}
				};

				xhr2.send(); // Move this line outside of the xhr.onload callback
			}
			// Set up the timer to call the function every 5000 milliseconds (5 seconds)
			setInterval(fetchDataAndUpdateDiv,keep_alive, 5000);
        });
    </script>
</head>
<body>
    <h2>Chat</h2>

    <form id="myForm">
        <label for="from">from:</label>
        <input type="text" name="from" id="from" required>
        <br>

        <label for="receiver">to:</label>
        <input type="text" name="receiver" id="receiver" required>
        <br>

        <label for="message">Message:</label>
        <textarea name="message" id="message" rows="4" required></textarea>
        <br>

        <input type="submit" value="Submit">
    </form>

    <div id="response"></div>
</body>
</html>
