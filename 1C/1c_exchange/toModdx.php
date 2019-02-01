<?php
ini_set('display_errors','On');
error_reporting(E_ALL);
//error_reporting(0);





$conf = array (
	'catalog_root'     	=> 7,
	'catalog_template' 	=> 5,
	'tv_1cid'          	=> 27,
	'tv_ed'            	=> 28,
	'tv_images'        	=> 17,
	'tv_article'       	=> 18,
	'tv_price'         	=> 13,
	'tv_in_stock'      	=> 26,
	'vendor'      		=> 20,
	'pricetype'      	=> 'b10d49a6-581a-11e7-b217-2c4d54564134', //задать 

	'host' 		=> 'u359859.mysql.masterhost.ru', 
    'user' 		=> 'u359859_lotus', 
    'password' 	=> 'FREpI79O-d.', 
    'db' 		=> 'u359859_lotus',
    'prefix' 	=> 'lotus_',

);


$DBH = connectPDO($conf);




$itms= $modx->db->query("SELECT * FROM ". $modx->getFullTableName('_1c_') ." WHERE status='new' OR status='to_db' ORDER BY dt DESC LIMIT 2");
if ($modx->db->getRecordCount($itms) != 2 ) {
 	exit('che err');
}else {
	while ($row = $modx->db->getRow($itms)) {
		$files[$row['type']]['path'] = $row['file'];
		$files[$row['type']]['id'] = $row['id'];
	}
} 


$STH_1c = $DBH->prepare("UPDATE ".$conf['prefix']."_1c_ SET
	status='complete'
	WHERE id = :id");

/*
$files['import']['path'] = '2017-11/2017-11-28-17-21-14__import0_1.xml';
$files['import']['id'] = 999;
$files['offers']['path'] = '2017-11/2017-11-28-17-21-28__offers0_1.xml';
$files['offers']['id'] = 998;
*/

/*
pre($files);
*/
//$xml= new SimpleXmlIterator(MODX_BASE_PATH.'1c_exchange/xml/'.$files['import']['path'], null,true);

$xml= new SimpleXmlIterator('1c_exchange/xml/'.$files['import']['path'], null,true);

createPath(crutch($xml->{'Классификатор'}->{'Группы'}->{'Группа'}), $conf['catalog_root'] , $DBH  , $conf);

$xml = $xml->{'Каталог'}->{'Товары'}->{'Товар'};
if($xml->count()){
	$xml->rewind();
	while($row = $xml->current()) {
		createGoods($xml->current(), $DBH  , $conf);
		$xml->next();
	}
}

$STH_1c->execute(array(
	'id'=> $files['import']['id'], 
));

unset($xml);


$xml= new SimpleXmlIterator('1c_exchange/xml/'.$files['offers']['path'], null,true);
$xml = $xml->{'ПакетПредложений'}->{'Предложения'}->{'Предложение'};
if($xml->count()){
	$xml->rewind();
	while($row = $xml->current()) {
		refreshOffers($xml->current(), $DBH  , $conf);
		$xml->next();
	}
}

$STH_1c->execute(array(
	'id'=> $files['offers']['id'], 
));

unset($xml);

clearModxCache();
echo 'success'.PHP_EOL;





function refreshOffers($xml , $DBH, $conf){
	if (! $xml instanceof SimpleXMLIterator) return false;

 	 $STH_tv = $DBH->prepare("INSERT INTO  ".$conf['prefix']."site_tmplvar_contentvalues ( 
        tmplvarid , 
        contentid,
        value
        )
        values (
        :tmplvarid,
        :contentid,
        :value
        )
        ON DUPLICATE KEY UPDATE value = :valueu " , 
        array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
    );

	if (($idGood = getModxID($DBH , explode( '#' , (string)$xml->{'Ид'})[0] , $conf)) !== false )  {
		if (count($xml->{'Цены'}->{'Цена'})) {
			$price = false;
			foreach ($xml->{'Цены'}->{'Цена'} as $idxKEY => $idxPATH) {
				if ((string)$idxPATH->{'ИдТипаЦены'} == $conf['pricetype']) {
					$price =(string)$idxPATH->{'ЦенаЗаЕдиницу'};
					break;
				}
			}

			if ($price !== false) {
				$STH_tv->execute(array(
				    'tmplvarid'=> $conf['tv_price'], 
				    'contentid'=> $idGood,
				    'value'=> $price,
				    'valueu'=> $price
				));
			}
		}


		$STH_tv->execute(array(
		    'tmplvarid'=> $conf['tv_in_stock'], 
		    'contentid'=> $idGood,
		    'value'=> (string)$xml->{'Количество'},
		    'valueu'=> (string)$xml->{'Количество'}
		));
	
	}
}



 


function clearModxCache(){
    global $modx;
    ob_start();
    $modx->clearCache('full');
    include_once MODX_BASE_PATH . '/manager/processors/cache_sync.class.processor.php';
    $sync= new synccache();
    $sync->setCachepath( MODX_BASE_PATH . "/assets/cache/" );
    $sync->setReport( false );
    $sync->emptyCache();
    ob_end_clean();
}
   




function createGoods($xml , $DBH, $conf){
	if (! $xml instanceof SimpleXMLIterator) return false;
 	$STH_ins = $DBH->prepare("INSERT INTO ".$conf['prefix']."site_content SET
		pagetitle=:pagetitle,
		alias=:alias,
		parent=:parent,
		content=:content,
		isfolder=0,
		template=".$conf['catalog_template'].",
		published=1");
 	 $STH_upd = $DBH->prepare("UPDATE ".$conf['prefix']."site_content SET
		pagetitle=:pagetitle,
		parent=:parent,
		content=:content,
		isfolder=0,
		template=".$conf['catalog_template'].",
		published=1
		WHERE id = :id");
 	 $STH_tv = $DBH->prepare("INSERT INTO  ".$conf['prefix']."site_tmplvar_contentvalues ( 
        tmplvarid , 
        contentid,
        value
        )
        values (
        :tmplvarid,
        :contentid,
        :value
        )
        ON DUPLICATE KEY UPDATE value = :valueu " , 
        array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
    );
	if (($idGood = getModxID($DBH , (string)$xml->{'Ид'} , $conf)) !== false )  {
		$STH_upd->execute(array(
	        'pagetitle'=> (string)$xml->{'Наименование'},
	        'parent'=> getModxID($DBH , (string)$xml->{'Группы'}->{'Ид'}, $conf),
	        'content'=> (string)$xml->{'Описание'},
	        'id'=> getModxID($DBH , (string)$xml->{'Ид'}, $conf)
	    ));
	}else {
		if ($STH_ins->execute(array(
	        'pagetitle'=> (string)$xml->{'Наименование'},
	        'alias'=>  getAlias($DBH , (string)$xml->{'Наименование'} , $conf),
	        'parent'=> getModxID($DBH , (string)$xml->{'Группы'}->{'Ид'}, $conf),
	        'content'=> (string)$xml->{'Описание'}
	    ))) {
			$idGood = $DBH->lastInsertId(); 
	    }
	}
	if (is_numeric($idGood)) {
		$STH_tv->execute(array(
		    'tmplvarid'=> $conf['tv_1cid'], 
		    'contentid'=> $idGood,
		    'value'=> (string)$xml->{'Ид'},
		    'valueu'=> (string)$xml->{'Ид'}
		));
		$STH_tv->execute(array(
		    'tmplvarid'=> $conf['tv_article'], 
		    'contentid'=> $idGood,
		    'value'=> (string)$xml->{'Артикул'},
		    'valueu'=> (string)$xml->{'Артикул'}
		));
		$STH_tv->execute(array(
		    'tmplvarid'=> $conf['vendor'], 
		    'contentid'=> $idGood,
		    'value'=> (string)$xml->{'Изготовитель'}->{'Наименование'},
		    'valueu'=> (string)$xml->{'Изготовитель'}->{'Наименование'}
		));
		$imgCollector = '';
		$ext = '';
		if (count($xml->{'Картинка'})) {
			foreach ($xml->{'Картинка'} as $idxKEY => $idxPATH) {

				$ext= @end(explode('.' , (string)$idxPATH));
				$img= md5((string)$idxPATH).'.'.$ext;
				$imgfolder= 'x'.mb_substr($img,0,2);
				$img= 'assets/images/1c/'.$imgfolder.'/'.$img;
				$imgCollector .= $imgCollector == '' ? (string)$img : '||'.(string)$img;

			}
			if ($imgCollector !== '') {
				$STH_tv->execute(array(
				    'tmplvarid'=> $conf['tv_images'], 
				    'contentid'=> $idGood,
				    'value'=> $imgCollector,
				    'valueu'=> $imgCollector
				));
			}
		}
	}
}

















function getModxID($DBH , $id1c , $conf){
	$STH_find = $DBH->prepare("SELECT sc.id FROM ".$conf['prefix']."site_content AS sc
		INNER JOIN  ".$conf['prefix']."site_tmplvar_contentvalues AS tv ON tv.contentid = sc.id  
		WHERE tv.tmplvarid = ".$conf['tv_1cid']." AND tv.`value` =  :value LIMIT 1");

	$STH_find->execute(array(
	    'value'=> $id1c
	)); 
	if($STH_find->rowCount())  {
		if ($row = $STH_find->fetch()) {
			return $row['id'];
		}
	}
	return false;
}











function createPath($xml , $catalog_root, $DBH, $conf){
	if (! $xml instanceof SimpleXMLIterator) return false;

 	$STH_find = $DBH->prepare("SELECT sc.id FROM ".$conf['prefix']."site_content AS sc
		INNER JOIN  ".$conf['prefix']."site_tmplvar_contentvalues AS tv ON tv.contentid = sc.id  
		WHERE tv.tmplvarid = ".$conf['tv_1cid']." AND tv.`value` =  :value LIMIT 1");


 	$STH_ins = $DBH->prepare("INSERT INTO ".$conf['prefix']."site_content SET
		pagetitle=:pagetitle,
		alias=:alias,
		parent=:parent,
		isfolder=1,
		template=".$conf['catalog_template'].",
		published=1");


 	 $STH_tv = $DBH->prepare("INSERT INTO  ".$conf['prefix']."site_tmplvar_contentvalues ( 
        tmplvarid , 
        contentid,
        value
        )
        values (
        :tmplvarid,
        :contentid,
        :value
        )
        ON DUPLICATE KEY UPDATE value = :valueu " , 
        array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
    );

 	foreach ($xml as $key => $val) {
 		if (! $val instanceof SimpleXMLIterator) break;

	    if ( ($idcat = getModxID($DBH , (string)$val->{'Ид'} , $conf)) !== false )  {

	    }else {
	    	if ($STH_ins->execute(array(
		        'pagetitle'=> (string)$val->{'Наименование'},
		        'alias'=>  getAlias($DBH , (string)$val->{'Наименование'} , $conf),
		        'parent'=> $catalog_root
		    ))) {
	    		$idcat = $DBH->lastInsertId();
	    		$STH_tv->execute(array(
			        'tmplvarid'=> $conf['tv_1cid'], 
			        'contentid'=> $idcat,
			        'value'=> (string)$val->{'Ид'},
			        'valueu'=> (string)$val->{'Ид'}
			    )); 
		    }
	    }
 		if (null !==($inner = $val->{'Группы'}->{'Группа'}) && $inner instanceof SimpleXMLIterator ) {
 			createPath($inner , $idcat, $DBH, $conf);
 		}
 	}
}






function  getAlias($DBH , $txt , $conf){

    $trans = array("а"=>"a", "б"=>"b", "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e",
        "ё"=>"jo", "ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"jj", "к"=>"k", "л"=>"l",
        "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u",
        "ф"=>"f", "х"=>"kh", "ц"=>"c", "ч"=>"ch", "ш"=>"sh", "щ"=>"shh", "ы"=>"y",
        "э"=>"eh", "ю"=>"yu", "я"=>"ya", "А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g",
        "Д"=>"d", "Е"=>"e", "Ё"=>"jo", "Ж"=>"zh", "З"=>"z", "И"=>"i", "Й"=>"jj",
        "К"=>"k", "Л"=>"l", "М"=>"m", "Н"=>"n", "О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s",
        "Т"=>"t", "У"=>"u", "Ф"=>"f", "Х"=>"kh", "Ц"=>"c", "Ч"=>"ch", "Ш"=>"sh",
        "Щ"=>"shh", "Ы"=>"y", "Э"=>"eh", "Ю"=>"yu", "Я"=>"ya", " "=>"-", "."=>"-",
        ","=>"-", "_"=>"-", "+"=>"-", ":"=>"-", ";"=>"-", "!"=>"-", "?"=>"-");
        
    $alias= addslashes($txt);
    $alias= strip_tags(strtr($alias, $trans));
    $alias= preg_replace("/[^a-zA-Z0-9-]/", '', $alias);
    $alias= preg_replace('/([-]){2,}/', '-', $alias);
    $alias= trim($alias, '-');
    
    if(strlen($alias) > 20) $alias= trim(substr($alias, 0, 20), '-');
    
    $STH = $DBH->prepare("SELECT id FROM ".$conf['prefix']."site_content WHERE alias = :alias LIMIT 1");
    $STH->setFetchMode(PDO::FETCH_ASSOC);  

    do{
        $STH->execute(array('alias' => $alias )); 
        if($STH->rowCount() == 1) $alias .= rand(1, 9);
    }while(($STH->rowCount() == 1));
    if( ! $STH) $alias= false;
    return $alias;
}





function connectPDO($conf) {
    $DBH = false; 
    try {  
      $DBH = new PDO("mysql:host=$conf[host];dbname=$conf[db];charset=UTF8", $conf['user'], $conf['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));  

    }  
    catch(PDOException $e) {  
        echo $e->getMessage();  
    }
    return $DBH; 
}



function crutch($childs){
	if (count($childs) > 0) {
		foreach ($childs as $key => $val){
			if ($val->{'Ид'} == '7b1460f6-88ca-11e7-b220-2c4d54564134') {
				$childs = $val->{'Группы'}->{'Группа'};
			}
		}
	}
	return $childs;
}

 


function pre($pre){
	echo '<pre>';
	print_r($pre);
	echo '</pre>';
}
