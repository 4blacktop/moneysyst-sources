<?
// скрипт парсит урлы источника подписки
// из биржи манисиста http://www.moneysyst.biz
// в настройках вводим дату и куки
// в firefox - Tools > Options > закладка Privacy,
// ссылка remove individual cookies, ищешь moneysist,
// выделяешь куку манисиста, копируешь

// настройки
$date = "2012-08-14";
$cookie = "PHPSESSID=dfhgjdgjdghjdghjdgj";  // заменить на свое

// настройки задержки
// см. функцию get

// готовимся
header("Content-Type: text/html; charset=utf-8");
set_time_limit(0);
$mtime = microtime(true);
// ignore_user_abort(true);
echo '<pre>';
echo 'Стартуем парсинг<br />' . $date . '-' . time() . '<br /><br />'; flush();

$data = json_decode(get("idt=0&dt1=&dt2=&sel=0&profit[]=&profit[]=&rebill[]=&rebill[]=&dt1s=".$date."&dt2s=".$date."&price[]=&price[]=&dt1r=&dt2r=&opr=7",$cookie));
// print_r($data);
if($data != null) {
	foreach($data as $info) {
		// сохраним в массив
		$json = get("idv=".$info->idv."&dt=".$date,$cookie);
		$domain = json_decode($json);
		$all[] = $json;
		if($domain->eng!="") {
			echo $domain->eng . '<br /><br />'; flush();
			$result[] = $domain->eng . "\r\n";
			$detail[] = $domain->eng . "\t" . $domain->qry . "\r\n";
			print_r($domain);
			}
		if (file_exists("stop.txt")) {
			exit();
			}
		// echo "<br />" . ($detail);
		}
	}
	
// обработаем массив и запишем его в файл
foreach(array_count_values($result) as $key=>$value){
	$out[] = $value . "\t" . $key;
    }
rsort($out);

if ($all !=null) {file_put_contents('ms-urls/' . $date . '-' . date("Ymd-Hi") . '-all.txt', $all);}
if ($out !=null) {file_put_contents('ms-urls/' . $date . '-' . date("Ymd-Hi") . '.txt', $out);}
if ($detail !=null) {file_put_contents('ms-urls/' . $date . '-' . date("Ymd-Hi") . '-ids.txt', $detail);}

// выведем статистику
echo '<br /><br />Время работы скрипта: ' . round((microtime(true) - $mtime) * 1, 4) . ' с.';
echo '<br />Прочитано url: ' . count($result);
echo '<br />Сохранено url: ' . count($out);
echo '</pre>';

function get($data,$cookie) {
	$pauseMin = 0;
	$pauseMax = 0;

 	// случайный прокси
	$proxyList = array ();
	$proxyList = file("proxyok.txt");
	$proxy = $proxyList[array_rand ($proxyList)];

    $ch = curl_init ( "http://moneysyst.biz/p-trade-tender.php" );
	curl_setopt ($ch, CURLOPT_PROXY, $proxy); 
    curl_setopt ($ch, CURLOPT_ENCODING , "gzip");
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3' );
    curl_setopt ($ch, CURLOPT_TIMEOUT, 20 );
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1 );
    curl_setopt ($ch, CURLOPT_HEADER, 0 );
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data );
    curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 10);	
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
	
	
	// недавно добавлено
	curl_setopt ( $ch, CURLOPT_REFERER, "http://moneysyst.biz/p-trade-tender.php"); 
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	
	$pause = rand ($pauseMin,$pauseMax); 
	echo "<br />$proxy + $pause с.:<br />";
	sleep($pause);
    $res = curl_exec ( $ch );
    curl_close ( $ch );
	print_r($res);
	echo "<br /><br />"; flush();
return $res;
}
?> 