<?php
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting())) {
        return;
    }

    http_response_code(500);

    echo "\n\n";
    debug_print_backtrace();
    echo "\nError [Errno ${errno}]: ${errstr}\n";
    echo "\n\n";

    exit;
});

http_response_code(500);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo file_get_contents(__FILE__);
    exit;
}

header('Content-Type: application/octet-stream');

$request = file_get_contents('php://input');
$request_length = strlen($request);

$servers = [
    // Cloudflare DNS
    '::ffff:1.1.1.1', '2606:4700:4700::1111',
    '::ffff:1.0.0.1', '2606:4700:4700::1001',

    // Google DNS
    '::ffff:8.8.8.8', '2001:4860:4860::8888',
    '::ffff:8.8.4.4', '2001:4860:4860::8844',

    // Quad9 DNS
    '::ffff:9.9.9.9', '2620:fe::fe',
    '::ffff:149.112.112.112', '2620:fe::9',

    // OpenDNS
    '::ffff:208.67.222.222', '2620:0:ccc::2',
    '::ffff:208.67.220.220', '2620:0:ccd::2',

    // Comodo DNS
    '::ffff:8.26.56.26',
    '::ffff:8.20.247.20',

    // dns.sb DNS
    '::ffff:185.222.222.222', '2a09::',
    '::ffff:185.184.222.222', '2a09::1',

    // Quad101 DNS
    '::ffff:101.101.101.101', '2001:de4::101',
    '::ffff:101.102.103.104', '2001:de4::102',

    // OpenNIC DNS
    '::ffff:134.195.4.2', '2604:ffc0::',
    '::ffff:5.132.191.104', '2a03:3180:f:7::dfc6:cfb7',
    '::ffff:172.105.162.206', '2400:8907::f03c:92ff:fee2:87ff',
    '::ffff:198.100.148.224', '2607:5300:60:be0::1',
    '::ffff:176.9.37.132', '2a01:4f8:161:3441::1',
    '::ffff:51.38.99.35', '2001:41d0:701:1100::1ccb',
    '::ffff:168.119.163.124', '2a01:4f8:c2c:ca7c::1',
    '::ffff:185.84.81.194', '2a02:248:2:41c0:5054:ff:fe80:88',
    '::ffff:116.203.147.31',
    '::ffff:78.47.243.3', '2a01:4f8:1c0c:80c9::1',
    '::ffff:95.217.16.205', '2a01:4f9:c010:6093::3485',
    #'::ffff:95.217.190.236',
    '::ffff:87.98.175.85', '2001:41d0:0002:7e59::106',
    '::ffff:51.255.211.146',
    '::ffff:145.239.92.241', '2001:41d0:601:1100::3485',
    #'::ffff:91.217.137.37', '2001:67c:13e4:1::37',
    '::ffff:185.52.0.55', '2a00:d880:5:1ea::a85b',
    '::ffff:176.126.70.119', '2a00:1a28:1157:3ef::2',
    '::ffff:46.21.150.56', '2604:4500:8:5::4a9e:f61b',
    '::ffff:162.243.19.47', '2604:a880:0:1010::b:4001',
    '::ffff:147.135.115.88', '2604:2dc0:101:200::465',
    '::ffff:58.6.115.42', #'2001:470:8388:2:20e:2eff:fe63:d4a9',
    '::ffff:58.6.115.43', '2001:470:1f07:38b::1',
    '::ffff:119.31.230.42', '2001:470:1f10:c6::2001',
    #'::ffff:200.252.98.162',
    '::ffff:217.79.186.148',
    '::ffff:81.89.98.6',
    #'::ffff:78.159.101.37',
    '::ffff:203.167.220.153',
    '::ffff:82.229.244.191',
    '::ffff:216.87.84.211',
    '::ffff:66.244.95.20',
    '::ffff:207.192.69.155',
    #'::ffff:72.14.189.120',
];

{
    $obj = [];
    foreach ($servers as $it) $obj[$it] = 123;
    $servers = array_keys($obj);
}

$socket = socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    http_response_code(500);
    echo 'Cannot to create a socket.';
    exit;
}

$sends = 0;

socket_clear_error($socket);
foreach ($servers as $server) {
    @socket_sendto($socket, $request, $request_length, 0, $server, 53);

    $errno = socket_last_error($socket);
    socket_clear_error($socket);

    if ($errno) continue;
    $sends += 1;
}

if ($sends <= 0) {
    http_response_code(500);
    echo 'Cannot to send a packet.';
    exit;
}

$response = null;

// timeout: 10 seconds
if (!socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 10, 'usec' => 0])) {
    http_response_code(500);
    echo 'Cannot to setting socket timeout.';
    exit;
}

while (1) {
    $from_ip = '';
    $from_port = 0;
    $recv = '';

    $ok = socket_recvfrom($socket, $recv, 65536, 0, $from_ip, $from_port);
    if (!$ok) {
        http_response_code(500);
        echo 'Cannot to receive a packet.';
        exit;
    }

    if ($from_port != 53) {
        continue;
    }
    if (is_bool(array_search($from_ip, $servers))) {
        continue;
    }

    header("X-Forwarded-For: ${from_ip}#${from_port} (UDP)");
    $response = $recv;

    break;
}

$response_length = strlen($response);

http_response_code(200);
header("Content-Length: ${response_length}");
echo $response;
exit;

