<?php

$host = '127.0.0.1';
$port = 8080;
$null = NULL;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $host, $port);
socket_listen($socket);

$clients = [$socket];
$users = [];

echo "========================================\n";
echo " WebSocket Chat Server запущено!\n";
echo " Слухаю порт: $port\n";
echo " Для зупинки натисніть Ctrl+C\n";
echo "========================================\n\n";

while (true) {
    $changed = $clients;
    socket_select($changed, $null, $null, 0, 10);

    if (in_array($socket, $changed)) {
        $socket_new = socket_accept($socket);
        $clients[] = $socket_new;

        socket_recv($socket_new, $header, 1024, 0);
        perform_handshake($header, $socket_new, $host, $port);

        socket_getpeername($socket_new, $ip);
        echo "--> Нове підключення з IP: $ip\n";

        $found_socket = array_search($socket, $changed);
        unset($changed[$found_socket]);
    }

    foreach ($changed as $changed_socket) {
        $bytes = @socket_recv($changed_socket, $buf, 2048, 0);

        if ($bytes === false || $bytes == 0) {
            $found_socket = array_search($changed_socket, $clients);
            $userId = (int) $changed_socket;
            $username = $users[$userId] ?? 'Анонім';
            echo "<-- Користувач '$username' відключився.\n";

            unset($clients[$found_socket]);
            if (isset($users[$userId]))
                unset($users[$userId]);
            continue;
        }

        $received_text = unmask($buf);
        $tst_msg = json_decode($received_text, true);

        if ($tst_msg) {
            $type = $tst_msg['type'] ?? '';
            $senderSocketId = (int) $changed_socket;

            if ($type === 'register') {
                $users[$senderSocketId] = $tst_msg['username'];
                echo "[СИСТЕМА] Зареєстровано користувача: {$tst_msg['username']}\n";
            } else if ($type === 'chat') {
                $senderName = $users[$senderSocketId] ?? 'Анонім';
                $recipientName = trim($tst_msg['recipient'] ?? '');

                $response = [
                    'type' => 'chat',
                    'sender' => $senderName,
                    'message' => $tst_msg['message'],
                    'date' => $tst_msg['date'],
                    'isPrivate' => !empty($recipientName)
                ];
                $response_text = mask(json_encode($response));

                if (!empty($recipientName)) {
                    $recipientSocketId = array_search($recipientName, $users);
                    if ($recipientSocketId !== false) {
                        foreach ($clients as $client) {
                            if ((int) $client == $recipientSocketId) {
                                @socket_write($client, $response_text, strlen($response_text));
                            }
                        }
                        echo "[ТЕТ-А-ТЕТ] $senderName -> $recipientName: {$tst_msg['message']}\n";
                    }
                    @socket_write($changed_socket, $response_text, strlen($response_text));
                } else {
                    echo "[ЗАГАЛЬНИЙ] $senderName: {$tst_msg['message']}\n";
                    foreach ($clients as $client) {
                        if ($client != $socket) {
                            @socket_write($client, $response_text, strlen($response_text));
                        }
                    }
                }
            }
        }
    }
}

function perform_handshake($receved_header, $client_conn, $host, $port)
{
    $headers = array();
    $lines = preg_split("/\r\n/", $receved_header);
    foreach ($lines as $line) {
        $line = chop($line);
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $headers[$matches[1]] = $matches[2];
        }
    }
    if (!isset($headers['Sec-WebSocket-Key']))
        return;
    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "WebSocket-Origin: $host\r\n" .
        "WebSocket-Location: ws://$host:$port/socket_server.php\r\n" .
        "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    socket_write($client_conn, $upgrade, strlen($upgrade));
}

function unmask($text)
{
    if (strlen($text) < 2)
        return "";
    $length = ord($text[1]) & 127;
    if ($length == 126) {
        $masks = substr($text, 4, 4);
        $data = substr($text, 8);
    } elseif ($length == 127) {
        $masks = substr($text, 10, 4);
        $data = substr($text, 14);
    } else {
        $masks = substr($text, 2, 4);
        $data = substr($text, 6);
    }
    $decoded = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $decoded .= $data[$i] ^ $masks[$i % 4];
    }
    return $decoded;
}

function mask($text)
{
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);
    if ($length <= 125)
        $header = pack('CC', $b1, $length);
    elseif ($length > 125 && $length < 65536)
        $header = pack('CCn', $b1, 126, $length);
    else
        $header = pack('CCNN', $b1, 127, $length);
    return $header . $text;
}