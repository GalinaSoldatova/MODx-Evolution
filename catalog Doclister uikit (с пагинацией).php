<?php
$id = $modx->documentIdentifier;
$document = $modx->getDocument($id,'isfolder',1,0);
$isfolder = $document['isfolder'];

$tvList= 'cat-img, cat-cost, cat-cover, cat-spec, parser_brand_product, parser_code_product, parser_group_product, parser_id_page, parser_image_path, parser_in_stock, parser_matherial_product, parser_pack_amo_product, parser_pack_weight_product, parser_print_descr_product, parser_product_uniq_key, parser_status_product, parser_subproduct, parser_volume_product, parser_weight_product, parse_reserve';      // Перечисляем TV-параметры, необходимые для вывода 
$tplCat = 'cat-tpl';                    // шаблон категории
$tplCatItem = 'cat-tpl-item';           // шаблон карточки в каталоге
$tvPrefix = ''; // Чтобы параметвы выводились без префикса
    
$categories = 
    '<div class="uk-child-width-1-4@s uk-child-width-1" data-uk-grid>'.
    $modx->runSnippet('DocLister',array(
            'tvList'  => $tvList,   
            'tpl'     => $tplCat, 
            'orderBy' => 'menuindex ASC',
            'filters' => ' AND(content:isfolder:=:1) ',
            'tvPrefix' => 'tv',
            'prepare' => 'catalogItemPrepare'
         ))
    .'</div>';
$items = 
    '<div class="uk-child-width-1-4@s uk-child-width-1" data-uk-grid>'.
    $modx->runSnippet('DocLister',array(
            'tvList' => $tvList,
            'tpl'    => $tplCatItem, 
            'orderBy'=> 'menuindex ASC',
            'filters'=> ' AND(content:isfolder:=:0) ',
            'tvPrefix' => 'tv',
            'prepare' => 'catalogItemPrepare',
            'paginate' => 'pages',
            'display' => '20',
            'TplNextP' => '@CODE: <li><a href="[+link+]"><span uk-pagination-next></span></a></li>',
            'TplPrevP' => '@CODE: <li><a href="[+link+]"><span uk-pagination-previous></span></a></li>',
            'TplPage' => '@CODE: <li><a href="[+link+]">[+num+]</a></li>',
            'TplCurrentPage' =>  '@CODE:<li class="uk-active"><span>[+num+]</span></li>',
            'TplWrapPaginate' => '@CODE:<div class="pages"><ul class="uk-pagination" uk-margin>[+wrap+]</ul></div>',
            'TplDotsPage' => '@CODE:  <li class="uk-disabled"><span>...</span></li>',
            'pageAdjacents' => '1'
        ))
    .'</div>[+pages+]';

if ($isfolder == 1) {echo $categories; echo $items;} else {echo "{{item-content}}";}
