<?php

header('Content-Type: application/json');



$assets = [

    'BTC' => ['ticker' => 'BTCUSDT', 'symbol' => 'bitcoin'],

    'ETH' => ['ticker' => 'ETHUSDT', 'symbol' => 'ethereum'],

    'USDT' => ['ticker' => null, 'symbol' => 'tether']

];



$currencies = ['USD', 'MXN'];



$result = [];



// Primero obtener USD/MXN desde Fixer.io (para convertir)

$fixerApiKey = '9d6323700a7e2c2a794682393667bef6';

$chFixer = curl_init();

curl_setopt($chFixer, CURLOPT_URL, "https://data.fixer.io/api/latest?access_key=$fixerApiKey&symbols=USD,MXN");

curl_setopt($chFixer, CURLOPT_RETURNTRANSFER, 1);

$responseFixer = curl_exec($chFixer);

curl_close($chFixer);



$fixerData = json_decode($responseFixer, true);

$usdToMxn = $fixerData['success'] ? ($fixerData['rates']['MXN'] / $fixerData['rates']['USD']) : 0;



foreach ($assets as $asset => $config) {

    $precioUsd = null;

    $precioMxn = null;

    $cambio24h = null;



    // Intentar con Binance (más preciso)

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.binance.com/api/v3/ticker/price?symbol=" . $config['ticker']);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [

        "User-Agent: Mozilla/5.0"

    ]);



    $response = curl_exec($ch);

    $err = curl_error($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);



    if (!$err && $httpCode === 200) {

        $data = json_decode($response, true);

        if (isset($data['price'])) {

            $precioUsd = floatval($data['price']);

            $precioMxn = $precioUsd * $usdToMxn;

        }

    }



    // Si Binance falla, usar CoinGecko como respaldo

    if ($precioUsd === null) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.coingecko.com/api/v3/simple/price?ids=" . $config['symbol'] . "&vs_currencies=usd&include_24hr_change=true");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            "User-Agent: Mozilla/5.0"

        ]);



        $response = curl_exec($ch);

        $err = curl_error($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);



        if (!$err && $httpCode === 200) {

            $data = json_decode($response, true);

            if (isset($data[$config['symbol']]['usd'])) {

                $precioUsd = $data[$config['symbol']]['usd'];

                $precioMxn = $precioUsd * $usdToMxn;

                $cambio24h = $data[$config['symbol']]['usd_24h_change'] ?? 0;

            }

        }

    } else {

        // Si Binance funciona, obtener el cambio porcentual de CoinGecko

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.coingecko.com/api/v3/simple/price?ids=" . $config['symbol'] . "&vs_currencies=usd&include_24hr_change=true");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            "User-Agent: Mozilla/5.0"

        ]);



        $response = curl_exec($ch);

        $err = curl_error($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);



        if (!$err && $httpCode === 200) {

            $data = json_decode($response, true);

            if (isset($data[$config['symbol']]['usd_24h_change'])) {

                $cambio24h = $data[$config['symbol']]['usd_24h_change'] ?? 0;

            }

        }

    }



    if ($precioUsd !== null) {

        $result[$asset] = [

            'USD' => $precioUsd,

            'MXN' => $precioMxn,

            'percent_change_24h' => $cambio24h

        ];

    }

}



echo json_encode($result, JSON_PRETTY_PRINT);

