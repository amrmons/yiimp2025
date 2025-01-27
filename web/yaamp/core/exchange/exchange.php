<?php

/* A REST/JSON API IS NOT SUPPOSED TO RETURN HTML AND CSS! MORONS!!!! */
function strip_data($data)
{
	$out = strip_tags($data);
	$out = preg_replace("#[\t ]+#", " ", $out);
	$out = preg_replace("# [\r\n]+#", "\n", $out);
	$out = preg_replace("#[\r\n]+#", "\n", $out);
	if (strpos($out, 'CloudFlare') !== false) $out = 'CloudFlare error';
	if (strpos($out, 'DDoS protection by Cloudflare') !== false) $out = 'CloudFlare error';
	if (strpos($out, '500 Error') !== false) $out = '500 Error';
	return $out;
}

require_once("altmarkets.php");
require_once("bitstamp.php");
require_once("cexio.php");
require_once("exbitron.php");
require_once("escodex.php");
require_once("gateio.php");
require_once("kraken.php");
require_once("poloniex.php");
require_once("yobit.php");
require_once("shapeshift.php");
require_once("jubi.php");
require_once("binance.php");
require_once("hitbtc.php");
require_once("kucoin.php");
require_once("xeggex.php");
require_once("nonkyc.php");
require_once("tradeogre.php");
require_once("safetrade.php");
require_once("swiftex.php");
require_once("bibox.php");


/* Format an exchange coin Url */
function getMarketUrl($coin, $marketName)
{
	$symbol = $coin->getOfficialSymbol();
	$lowsymbol = strtolower($symbol);
	$base = 'BTC';

	$market = trim($marketName);
	if (strpos($marketName, ' ')) {
		$parts = explode(' ',$marketName);
		$market = $parts[0];
		$base = $parts[1];
		if (empty($base)) {
			debuglog("warning: invalid market name '$marketName'");
			$base = dboscalar(
			"SELECT base_coin FROM markets WHERE coinid=:id AND name=:name", array(
				':id'=>$coin->id, ':name'=>$marketName,
			));
		}
	}

	$lowbase = strtolower($base);

	if($market == 'altmarkets')
		$url = "https://v2.altmarkets.io/trading/{$lowsymbol}{$lowbase}";
	else if($market == 'bibox')
		$url = "https://www.bibox.com/exchange?coinPair={$symbol}_{$base}";
	else if($market == 'binance')
		$url = "https://www.binance.com/trade.html?symbol={$symbol}_{$base}";
	else if($market == 'poloniex')
		$url = "https://poloniex.com/exchange#{$lowbase}_{$lowsymbol}";
	else if($market == 'cexio')
		$url = "https://cex.io/trade/{$symbol}-{$base}";
	else if($market == 'exbitron')
		$url = "https://www.exbitron.com/trading/{$lowsymbol}{$lowbase}";
	else if($market == 'escodex')
		$url = "https://wallet.escodex.com/market/ESCODEX.{$symbol}_ESCODEX.{$base}";
	else if($market == 'gateio')
		$url = "https://gate.io/trade/{$symbol}_{$base}";
	else if($market == 'jubi')
		$url = "http://jubi.com/coin/{$lowsymbol}";
	else if($market == 'hitbtc')
		$url = "https://hitbtc.com/exchange/{$symbol}-to-{$base}";
	else if($market == 'kucoin')
		$url = "https://www.kucoin.com/#/trade.pro/{$symbol}-{$base}";
	else if ($market == 'xeggex')
		$url = "https://xeggex.com/market/{$symbol}_{$base}";
	else if ($market == 'nonkyc')
		$url = "https://nonkyc.io/market/{$symbol}_{$base}";
	else if($market == 'tradeogre')
		$url = "https://tradeogre.com/exchange/{$base}-{$symbol}";
	else if($market == 'yobit')
		$url = "https://yobit.net/en/trade/{$symbol}/{$base}";
	else if($market == 'swiftex')
		$url = "https://swiftex.co/trading/{$lowsymbol}-{$lowbase}";	
	else if($market == 'safetrade')
		$url = "https://safetrade.com/exchange/{$lowsymbol}-{$lowbase}";	
	else
		$url = "";

	return $url;
}

// $market can be a db_markets or a string (symbol)
function exchange_update_market($exchange, $market)
{
	$fn_update = str_replace('-','',$exchange.'_update_market');
	if (function_exists($fn_update)) {
		return $fn_update($market);
	} else {
		debuglog(__FUNCTION__.': '.$fn_update.'() not implemented');
		user()->setFlash('error', $fn_update.'() not yet implemented');
		return false;
	}
}

// used to manually update one market price
function exchange_update_market_by_id($idmarket)
{
	$market = getdbo('db_markets', $idmarket);
	if (!$market) return false;

	return exchange_update_market($market->name, $market);
}
