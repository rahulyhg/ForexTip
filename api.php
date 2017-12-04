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
    foreach ($data as $index => $d) {
        if (((time() * 1000) - $d['dateTime']) > (5 * 24 * 60 * 60 * 1000)) continue;
        $tmp['lot'] = $d['stdLotds'];
        $tmp['price'] = $d['entryRate'];
        $tmp['digit'] = $d['pipMultiplier'];
        $top20TipByCurrentcy[$d['currency']][$d['tradeType']][$index] = $tmp;
    }
}

$result = [];
foreach ($top20TipByCurrentcy as $currency => $type) {
    foreach ($type as $index => $d) {
        $lot = 0;
        $price = 0;
        $digit = 3;
        foreach ($d as $t) {
            $lot += $t['lot'];
            $price += ($t['price'] * $t['lot']);
            if ($t['digit'] == 10000) $digit = 5;
        }
        if ($lot < 1) continue;
        $price /= $lot;
        $tmp = [];
        $tmp['lot'] = $lot;
        $tmp['price'] = number_format($price, $digit);
        $result[$currency][$index] = $tmp;
    }
}
header('Content-Type: application/json');
print json_encode($result);