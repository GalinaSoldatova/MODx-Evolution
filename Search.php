<?php
$prefix_table= 'scenar_'; 			//префикс таблиц БД
$template = '3'; 					//шаблоны, участвующие в выборке (можно через запятую перечислять)
$tvParams = '1,2,3';				//если нужно искать по определенным полям (артикул, подробное описание и т.д.)	(можно через запятую перечислять)
$tpl = 'evoSearch';					//шаблон для вывода
$tvList= 'img,cat_price';  			//TV-параметры, необходимые для вывода 
	
if ( mb_strlen($_GET['search'] , "UTF-8") >= 1 ) {
	
	if( ! $id) $id= $modx->documentIdentifier; 
	
	$documents = '';
	$needly = $modx->db->escape(addslashes(urldecode($_GET['search'])));
	$output = array();
	$needlyStrict = '';
	$re = '/([a-zA-Zа-яА-Я\d]*)/ui';
	
	preg_match_all($re, $needly, $matches);
	
	if (is_array($matches[0]) && count($matches[0])>0){
		foreach($matches[0] as $elem){
			$elem = trim($elem);
			if ($elem) {
				$needlyStrict .="(sc.pagetitle LIKE '%".$elem."%' OR sc.content LIKE '%".$elem."%') AND ";
			}
		}
	}
	
	if (is_numeric( ($parentCat = $_GET['cat']) )) {
		$needlyStrict .="sc.parent = ".$parentCat." AND ";
	}
	
	$sql = "SELECT sc.id, sc.pagetitle, sc.createdon , sc.parent , prnt.pagetitle AS prntpgt FROM ".$prefix_table."site_content AS sc
		INNER JOIN  ".$prefix_table."site_content AS prnt ON sc.parent = prnt.id
		WHERE ".$needlyStrict."
		sc.template IN (".$template.") AND sc.published = 1  AND sc.isfolder = 0  AND sc.deleted = 0  AND sc.type = 'document' 
		LIMIT 250";	
	
	$result = $modx->db->query($sql);
	$i = 0;
	
	$collect = array();
	while( $row = $modx->db->getRow( $result ) ) {
		
		//print_r($row);
		$tvArr = array();
		$sqlTV = "SELECT tv.value, t.name FROM ".$prefix_table."site_tmplvar_contentvalues AS tv
		INNER JOIN ".$prefix_table."site_tmplvars AS t ON tv.tmplvarid = t.id
		WHERE tv.contentid = ".$row['id']. ($tvParams ? " AND tv.tmplvarid IN (".$tvParams.") " : "")."
		LIMIT 20";
		
		$resultTV = $modx->db->query($sqlTV);
		while( $rowTV = $modx->db->getRow( $resultTV ) ) {
			$tvArr[$rowTV['name'] ] = $rowTV['value'];
		}
		
		$collect[$i]['data'] = $row;
		$collect[$i]['TV'] = $tvArr;	
		
		if (is_null($tvArr['availability'])) {
			$collect[$i]['TV']['availability'] = 1;
		}
		
		
		$i++;
	}
	
	
	$collectCat = array();
	
	if (is_array($collect) && count($collect) > 0) {
		foreach ($collect as $key => $row) {
			if (!array_key_exists ($row['data']['parent'] , $collectCat)) $collectCat[$row['data']['parent'] ] =$row['data']['prntpgt'];
			$documents .= $documents == '' ? $row['data']['id'] : ','.$row['data']['id'];
		}
		
	}
	
}



echo '<div class="searchTit">Найдено по запросу &laquo; <a href="'.$modx->makeUrl($modx->documentIdentifier).'?search='.$needly.'">'.urldecode($needly).'</a> &raquo;: '.count($collect).'</div>';


echo '<div class="searchTitMin">В категориях:</div>';

echo '<div class="searchCats">';
if (is_array($collectCat) && count($collectCat) > 0) {
	foreach ($collectCat as $key => $row) {
		echo '<a href="'.$modx->makeUrl($modx->documentIdentifier).'?search='.$needly.'&cat='.$key.'">'.$row.'</a>';
	}
}
echo '</div>';
echo '<div class="catalog">';

echo  $modx->runSnippet('Ditto',array(	'startID'	=>'parents',
									  'documents'	=> $documents,
									  'noResults'	=> ' ',
									  'sortDir' 	=> 'ASC',
									  'tpl'       => $tpl,
									  'depth'     => 5
									 ));
/*
echo  $modx->runSnippet('DocLister',array(	'idType'	=>'documents',
									  'documents'	=> $documents,
									  'sortDir' 	=> 'ASC',
									  'tpl'       => $tpl,
									  'tvPrefix'  => $tvPrefix,
										'tvList'    => $tvList,     
									  'depth'     => 5
									 ));
*/

echo '</div>';




<style>

/* ============= Search =============== */


.searchTit {
    font-size: 19px;
    font-weight: 100;
}

.searchTit a {
   /* color: #ca0072;*/
    font-weight: 500;
}


.searchTitMin {
    font-weight: 100;
    margin-top: 20px;
}



.searchCats {
   /* width: 800px;*/
    column-count: 4;
    padding: 10px;
    background: #f5f5f5;
    margin: 8px 0 40px;
}

.searchCats  a {
    display: block;
    -webkit-column-break-inside: avoid;
    page-break-inside: avoid;
    break-inside: avoid;
	text-decoration: none;
}

/* ============= Search - END =============== */
</style>