<?php
$id = $modx->documentIdentifier;
$document = $modx->getDocument($id,'isfolder',1,0);
$isfolder = $document['isfolder'];

$tvList= 'img,cat_title,cat_price';  	// Перечисляем TV-параметры, необходимые для вывода 
$tplCat = 'tplCat'; 					// шаблон категории
$tplCatItem = 'tplCatItem'; 			// шаблон карточки в каталоге
$tvPrefix = '';						// префикс для вывода TV-параметров

$categories = $modx->runSnippet('DocLister',array(
    'tvList'    => $tvList,   
    'tpl'       => $tplCat, 
    'orderBy'   => 'menuindex ASC',
    'filters'   => ' AND(content:isfolder:=:1) ',
    'tvPrefix'	=> $tvPrefix,
    'paginate' => 'pages',
	'display' => '12',
	'TplNextP' => '',
	'TplPrevP' => '',
));

$items = $modx->runSnippet('DocLister',array(
    'tvList'    => $tvList,
    'tpl'       => $tplCatItem, 
    'orderBy'   => 'menuindex ASC',
    'filters'   => ' AND(content:isfolder:=:0) ',
    'tvPrefix'  => $tvPrefix,
    'paginate' => 'pages',
	'display' => '12',
	'TplNextP' => '',
	'TplPrevP' => '',
));

if ($isfolder == 1) {echo $categories; echo $items;} else {echo "{{item_content}}";}
