<?php
require_once('rest.inc.php');

function getTop20Id()
{
    $url = 'https://www.zulutrade.com/zulutrade-client/v2/api/providers/performance/search';
    $dataJson = '{"timeFrame":10000,"minPips":0.1,"tradingExotics":false,"minWinTradesPercentage":80,"sortBy":"amountFollowing","sortAsc":false,"size":20,"page":0,"flavor":"global"}';
    $top20Obj = RestCurl::post($url, $dataJson);
    $top20Data = $top20Obj['data']->result;
    $top20IdArray = [];
    foreach ($top20Data as $d) {
        $tmp['id'] = ($d->trader->providerId);
        $top20IdArray[] = $tmp;
    }

    file_put_contents('data/0_Top20ID.txt', json_encode($top20IdArray));
}

function getDataFromId()
{
    $top20ID = json_decode(file_get_contents('data/0_Top20ID.txt'), true);
    $returnArray = [];
    foreach ($top20ID as $index => $id) {
        $url = 'https://www.zulutrade.com/zulutrade-client/traders/api/providers/' . $id['id'] . '/openTrades';
        $top20Obj = RestCurl::get($url);
        $top20Data = $top20Obj['data'];
        if (!$top20Obj['data']) continue;
        foreach ($top20Data as $data) {
            @$tmp['currency'] = $data->currencyName;
            @$tmp['dateTime'] = $data->dateTime;
            @$tmp['stdLotds'] = $data->stdLotds;
            @$tmp['tradeType'] = $data->tradeType;
            @$tmp['entryRate'] = $data->entryRate;
            @$tmp['pipMultiplier'] = $data->pipMultiplier;
            $returnArray[] = $tmp;
        }
    }

    file_put_contents('data/1_Top20Data.txt', json_encode($returnArray));
}

function formatDataByCurrency()
{
    $top20Data = json_decode(file_get_contents('data/1_Top20Data.txt'), true);
    $listCurrency = [];
    $returnArray = [];
    foreach ($top20Data as $index => $data) {
        if (((time() * 1000) - $data['dateTime']) > (5 * 24 * 60 * 60 * 1000)) continue;
        $currency = str_replace('/', '', $data['currency']);
        $listCurrency[$currency] = true;
        $tmp = [];
        $tmp['stdLotds'] = $data['stdLotds'];
        $tmp['entryRate'] = $data['entryRate'];
        $tmp['pipMultiplier'] = $data['pipMultiplier'];
        $returnArray[$currency][$data['tradeType']][] = $tmp;
    }
    $listCurrency = array_keys($listCurrency);
    file_put_contents('data/2_ListCurrency.txt', json_encode($listCurrency));
    file_put_contents('data/3_FormatDataByCurrency.txt', json_encode($returnArray));
}

function getCurrentPrice()
{
    $listCurrency = json_decode(file_get_contents('data/2_ListCurrency.txt'), true);
    $listCurrency = implode(',', $listCurrency);
    $priceObj = RestCurl::get('https://forex.1forge.com/1.0.2/quotes?pairs=' . $listCurrency . '&api_key=Jf3MVdUSbaHCVy1Tn9EFMAUdkosZhbJj');
    $returnArray = [];
    foreach ($priceObj['data'] as $d) {
        $tmp['price'] = $d->price;
        $tmp['bid'] = $d->bid;
        $tmp['ask'] = $d->ask;
        $returnArray[$d->symbol] = $tmp;
    }

    file_put_contents('data/4_CurrentPrice.txt', json_encode($returnArray));
}

function zipDataByCurrency()
{
    $formatDataByCurrency = json_decode(file_get_contents('data/3_FormatDataByCurrency.txt'), true);
    $currentPrice = json_decode(file_get_contents('data/4_CurrentPrice.txt'), true);
    $returnArray = [];
    foreach ($formatDataByCurrency as $currency => $dataObj) {
        foreach ($dataObj as $type => $data) {
            $totalLotByType = 0;
            $totalPriceByType = 0;
            $pipMultiplier = 0;
            foreach ($data as $order) {
                $totalLotByType += $order['stdLotds'];
                $totalPriceByType += ($order['stdLotds'] * $order['entryRate']);
                $pipMultiplier = $order['pipMultiplier'];
            }
            if ($totalLotByType < 1) continue;
            $averagePrice = ($totalPriceByType / $totalLotByType);
            $averagePrice = number_format($averagePrice, strlen($pipMultiplier));
            $floatingPips = ($currentPrice[$currency]['price'] - $averagePrice) * $pipMultiplier;
            $floatingPips = ($type == 'BUY') ? $floatingPips : $floatingPips * -1;
            $floatingPips = number_format($floatingPips, 1);
            $tmp['currency'] = $currency;
            $tmp['tradeType'] = $type;
            $tmp['stdLotds'] = $totalLotByType;
            $tmp['entryRate'] = (float)$averagePrice;
            $tmp['currentPrice'] = $currentPrice[$currency]['price'];
            $tmp['floatingPips'] = $floatingPips;
            $returnArray[$currency][$type] = $tmp;
        }
    }

    file_put_contents('data/5_ZipDataByCurrency.txt', json_encode($returnArray));
}

getTop20Id();
getDataFromId();
formatDataByCurrency();
getCurrentPrice();
zipDataByCurrency();


$zipDataByCurrency = json_decode(file_get_contents('data/5_ZipDataByCurrency.txt'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="30"/>
    <title>FX Signal</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <?php
            print '<table class="table table-bordered table-hover table-condensed" style="margin: auto; max-width: 650px;">';
            print '<thead>';
            print '<tr>';
            print '<th>Symbol</th>';
            print '<th>Type</th>';
            print '<th>Lot</th>';
            print '<th>Open Price</th>';
            print '<th>Curren Price</th>';
            print '<th>Floating Pips</th>';
            print '</tr>';
            print '</thead>';
            print '<tbody>';
            foreach ($zipDataByCurrency as $currentcy => $dataObj) {
                $tmp = 0;
                foreach ($dataObj as $type => $order) {
                    $tmp++;
                    $class = ($type == 'BUY') ? 'success' : 'danger';
                    print '<tr class="' . $class . '">';
                    print '<td>' . $currentcy . '</td>';
                    print '<td>' . $type . '</td>';
                    print '<td>' . $order['stdLotds'] . '</td>';
                    print '<td>' . $order['entryRate'] . '</td>';
                    print '<td>' . $order['currentPrice'] . '</td>';
                    print '<td>' . $order['floatingPips'] . '</td>';
                    print '</tr>';
                }
                if ($tmp) {
                    print "<tr>";
                    print "<td colspan='7'></td>";
                    print "</tr>";
                    print "<tr>";
                    print "<td colspan='7'></td>";
                    print "</tr>";
                }
            }
            print "</tbody>";
            print "</table>";
            ?>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>