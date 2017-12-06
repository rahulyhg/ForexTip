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
        $tmp['name'] = ($d->trader->profile->name);
        $top20IdArray[] = $tmp;
    }

    return $top20IdArray;
}

function getDataFromId($id)
{
    $url = 'https://www.zulutrade.com/zulutrade-client/traders/api/providers/' . $id . '/openTrades';
    $top20Obj = RestCurl::get($url);
    $top20Data = $top20Obj['data'];
    if (!$top20Obj['data']) return;
    foreach ($top20Data as $data) {
        @$tmp['currency'] = $data->currencyName;
        @$tmp['dateTime'] = $data->dateTime;
        @$tmp['stdLotds'] = $data->stdLotds;
        @$tmp['tradeType'] = $data->tradeType;
        @$tmp['entryRate'] = $data->entryRate;
        @$tmp['currentRate'] = $data->currentRate;
        @$tmp['floatingPips'] = $data->floatingPips;
        @$tmp['pipMultiplier'] = $data->pipMultiplier;
        $returnArray[] = $tmp;
    }

    return $returnArray;
}

$IdArray = getTop20Id();
$top20Tip = [];
foreach ($IdArray as $Id) {
    $data = getDataFromId($Id['id']);
    if ($data) {
        $top20Tip[$Id['id'] . ' - ' . $Id['name']] = $data;
    }
}
$top20TipByCurrentcy = [];
foreach ($top20Tip as $currentcy => $data) {
    foreach ($data as $d) {
        $top20TipByCurrentcy[$d['currency']][] = $d;
        sort($top20TipByCurrentcy[$d['currency']]);
    }
}
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
            print '<th>Time</th>';
            print '<th>Lot</th>';
            print '<th>Open Price</th>';
            print '<th>Curren Price</th>';
            print '<th>Floating Pips</th>';
            print '</tr>';
            print '</thead>';
            print '<tbody>';
            foreach ($top20TipByCurrentcy as $currentcy => $data) {
                $tmp = 0;
                foreach ($data as $d) {
                    if (((time() * 1000) - $d['dateTime']) > (5 * 24 * 60 * 60 * 1000)) continue;
                    $tmp++;
                    $digit = ($d['pipMultiplier'] == 10000) ? 5 : 3;
                    $class = ($d['tradeType'] == 'BUY') ? 'success' : 'danger';
                    print '<tr class="' . $class . '">';
                    print '<td>' . $d['currency'] . '</td>';
                    print '<td>' . $d['tradeType'] . '</td>';
                    print '<td>' . date("Y/m/d", $d['dateTime'] / 1000) . '</td>';
                    print '<td>' . $d['stdLotds'] . '</td>';
                    print '<td>' . number_format($d['entryRate'], $digit) . '</td>';
                    print '<td>' . number_format($d['currentRate'], $digit) . '</td>';
                    print '<td>' . $d['floatingPips'] . '</td>';
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