<?php

// COMODO_SERVER="http://localhost:8501 php demo.php"
$comodoServer = getenv('COMODO_SERVER') ?: 'http://localhost';

// file upload example
$eicar = sys_get_temp_dir() . '/' . uniqid() . '-eicar.com.txt';
file_put_contents($eicar, 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*');
$ch = curl_init($comodoServer);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['passwd' => curl_file_create('/etc/passwd'), 'eicar' => curl_file_create($eicar)]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo "Testing file upload:\n";
echo curl_exec($ch);
unset($ch);
unlink($eicar);

echo "\n";

// remote url example
$ch = curl_init($comodoServer);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['passwd' => 'https://en.wikipedia.org/wiki/Passwd', 'eicar' => 'http://www.eicar.org/download/eicar.com.txt']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo "Testing remote file scan:\n";
echo curl_exec($ch);
unset($ch);