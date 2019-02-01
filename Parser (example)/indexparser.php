<?php

header("Content-Type: text/html; charset=utf-8");

ini_set('display_errors','On');
error_reporting(E_ALL);
setlocale(LC_ALL, 'ru_RU.utf8');


$m = memory_get_peak_usage(); 
$time_start = microtime_float();


$conf = array(
    'host' => 'сервер', 
    'user' => 'пользователь', 
    'password' => 'пароль', 
    'db' => 'база данных'
);

$DBH = connectPDO($conf);

require('phpQuery/phpQuery.php');

$hostname = 'http://www.russkiyrostok.ru';


$state = file_get_contents("px_state.txt");


if ($state == 'ended'){
    if (date ("d", filemtime("px_state.txt")) != date("d", time())  ) {
        echo '--- Начало положено --- ';
        file_put_contents ("px_state.txt" , 'dload');
    }
}


if ($state == 'dload'){

    echo '--- Основная загрузка параметров --- ';

    $page = loadPage($hostname);
    $document = phpQuery::newDocument($page);
    try {
        $menu = getDOMChild('#block-menu-menu-catalog .content .menu li' , $document);
    } catch (Error $e) {
        echo "ФАТАЛ ЭРОР: " . $e->getMessage() . "\n";
    }

    $menuList = getMenuArr($menu);
    $countElArr = count($menuList);


    // ненужные разделы - 0,3,6,16,26,34,40,46,57,67

    unset($menuList[0]);	
    unset($menuList[3]);
    unset($menuList[6]);
    unset($menuList[16]);
    unset($menuList[26]); 
    unset($menuList[34]);
    unset($menuList[40]);
    unset($menuList[46]);
    unset($menuList[57]);
    unset($menuList[67]);
   
   // print_r($menuList);
  //  die();


    $step = file_get_contents("px_step.txt");

    if ($step >=  $countElArr) {
       	file_put_contents ("px_step.txt" , 0);
    	file_put_contents ("px_state.txt" , 'getimg');
        echo '<h1>КОНЕЦ</h1>';
    }else{
        echo 'ШАГ - >' . $step;
        echo '<br>';

        if (isset($menuList[$step])) {
            $goods = getProdsOnPage($menuList[$step]); // 0 - 12 

        //   print_r($goods);
        //   die();

            
    
            echo '<b>';
            echo 'ВСЕГО ТОВАРОВ - ' . count($goods);
            echo '</b>';

            addHeadToCat($goods , $DBH);

            //getParents($goods , $DBH );
            //saveGoods($goods , $DBH );
        }
        
       	$step++;
       	file_put_contents ("px_step.txt" , $step);
        
    }
}elseif ($state == 'getimg'){
    die();
    if (getImageAndDescription($DBH)) {
        echo '--- Изображения, описания и артикулы получены --- ';
        file_put_contents ("px_state.txt" , 'insertToBase');
    } else echo '--- Получение изображений, описаний и артикулов --- ';
}elseif ($state == 'insertToBase'){
   // print('stooooop');
   // die();
    echo '--- Добавление в базу --- ';
    insertToBase($DBH);
}





//поправила



function addHeadToCat ($goods,$DBH){

    
    $STH = $DBH->prepare("SELECT id FROM  rusrostok_site_content WHERE pagetitle LIKE :pagetitle AND isfolder=1 ORDER BY id ASC LIMIT 300"); //LEAVE 

    $STH->execute(array(
        'pagetitle'=> $goods['cats']
    )); 

    print $goods['cats'];

    if($STH->rowCount() > 0)  {
        while($row = $STH->fetch()) { 
            $catId = $row['id'];
        }
    }
    
    $STH_tv = $DBH->prepare("INSERT INTO rusrostok_site_tmplvar_contentvalues ( 
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

  //  echo 'добавляем цену - '.$good['price'].'<br>';

    $STH_tv->execute(array(
        'tmplvarid'=> 15, // шапка
        'contentid'=> $catId,
        'value'=> $goods['thead'],
        'valueu'=>  $goods['thead']
    )); 

    print  $goods['thead'];

}



function insertToBase ($DBH) {
    $STH = $DBH->prepare("SELECT * FROM  rusrostok__goods WHERE state = 0 ORDER BY id ASC LIMIT 300"); //LEAVE 
    $STHUpd = $DBH->prepare("UPDATE  rusrostok__goods SET state = 1 WHERE id = :id"); //LEAVE LIMIT
    $STH->execute(); 
    if($STH->rowCount() > 0)  {
        while($row = $STH->fetch()) { 
            $good = unserialize($row['good']);
           // pre($good);
            //$good['params']['Тип'] = $row['filter_type_value'];
            $productID = processedProduct($DBH , $good  , $row['image'] , $row['description'] );
           // $filtersIDs = processedFilter($DBH , $good['params'] , $productID);
            $STHUpd->execute(array('id' => $row['id'])); 
        }
    }else {
        file_put_contents ("px_state.txt" , 'ended');
    }
}


function processedProduct($DBH, $good , $image , $description){

	$STH = $DBH->prepare("SELECT id FROM rusrostok_site_content WHERE `pagetitle` = :sku LIMIT 1");
    
    $STH->execute(array(
        'sku'=> $good['name']
    )); 

 /*   $STH = $DBH->prepare("SELECT id FROM rusrostok_site_content WHERE `alias` LIKE '%:sku%' LIMIT 1");
    
    $STH->execute(array(
        'sku'=> $good['href']
    )); 
*/

    if($STH->rowCount() == 1 )  {

        if ($row = $STH->fetch()) { 
            echo 'updating';

            $productId = $row['id'];

           // echo $productId.'<br>';

            try {
                $DBH->beginTransaction();
                $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                $STH_sc = $DBH->prepare("UPDATE rusrostok_site_content SET  
                    pagetitle = :pagetitle ,
                    parent = :parent,
                    content = :content,
                    createdon =  '".time()."'
                    WHERE id = ".$productId." LIMIT 1 "
                );  

                // echo $description;
                // echo  $good['name'];

                $STH_sc->execute(array(
                    'pagetitle'=> $good['name'],
                    'content'=> $description,
                    'parent'=> $good['catsID'], //закомментить если категорию менять не нужно
                )); 
     
                $DBH->commit(); 

            }catch(PDOExecption $e) { 
                $DBH->rollback(); 
                print "Error!: " . $e->getMessage() . "</br>"; 
                exit("Exiting");
            }

        }
    }else {
        echo 'inserting';

        try { 
            $DBH->beginTransaction();
            $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $STH_sc = $DBH->prepare("INSERT INTO rusrostok_site_content ( 
                pagetitle ,  
                alias ,  
                published, 
                parent,
                isfolder,
                template,
                content,
                createdon
                ) 
                values (
                :pagetitle ,  
                :alias ,  
                1, 
                :parent,
                0,
                7, 
                :content,
                '".time()."'
                )" 
            );  

            // echo 'Описание - '.$description;

            echo 'наименование - '. $good['name'];

            $STH_sc->execute(array(
                'pagetitle'=> $good['name'],
                'alias'=> getAlias($DBH, $good['name']),
                'content'=> $description,
                'parent'=> $good['catsID'],
            )); 

            $productId = $DBH->lastInsertId();

            $DBH->commit(); 

        }catch(PDOExecption $e) { 
            $DBH->rollback(); 
            print "Error!: " . $e->getMessage() . "</br>"; 
            exit("Exiting");
        }
    }


    $STH_tv = $DBH->prepare("INSERT INTO rusrostok_site_tmplvar_contentvalues ( 
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

  //  echo 'добавляем цену - '.$good['price'].'<br>';

    $STH_tv->execute(array(
        'tmplvarid'=> 5, // картинка
        'contentid'=> $productId,
        'value'=> $image,
        'valueu'=> $image
    )); 

    //обычный шаблон
    if (isset($good['veg_period'])) {
        $STH_tv->execute(array(
            'tmplvarid'=> 17, // вегетационный период
            'contentid'=> $productId,
            'value'=> $good['veg_period'],
            'valueu'=> $good['veg_period']
        )); 
    }

    if (isset($good['ot_vusadki'])) {
        $STH_tv->execute(array(
            'tmplvarid'=> 9, // от высадки рассады
            'contentid'=> $productId,
            'value'=> $good['ot_vusadki'],
            'valueu'=> $good['ot_vusadki']
        )); 
    }

    if (isset($good['weight'])) {
        $STH_tv->execute(array(
            'tmplvarid'=> 10, // вес
            'contentid'=> $productId,
            'value'=> $good['weight'],
            'valueu'=> $good['weight']
        )); 
    }

    if (isset($good['type'])) {
        $STH_tv->execute(array(
            'tmplvarid'=> 18, // тип плода
            'contentid'=> $productId,
            'value'=> $good['type'],
            'valueu'=> $good['type']
        )); 
    }

    if (isset($good['figure'])) {
   
        $STH_tv->execute(array(
            'tmplvarid'=> 19, // форма плода
            'contentid'=> $productId,
            'value'=> $good['figure'],
            'valueu'=> $good['figure']
        )); 

    }

    //шаблон для зелени

    if (isset($good['dop_info'])) {

        $STH_tv->execute(array(
            'tmplvarid'=> 20, // дополнительная информация
            'contentid'=> $productId,
            'value'=> $good['dop_info'],
            'valueu'=> $good['dop_info']
        )); 
    }


     //шаблон для кукурузы

    if (isset($good['after_vshodov'])) {

        $STH_tv->execute(array(
            'tmplvarid'=> 21, // после всходов
            'contentid'=> $productId,
            'value'=> $good['after_vshodov'],
            'valueu'=> $good['after_vshodov']
        )); 
    }


    if (isset($good['temperature'])) {

        $STH_tv->execute(array(
            'tmplvarid'=> 22, // сумма активных температур
            'contentid'=> $productId,
            'value'=> $good['temperature'],
            'valueu'=> $good['temperature']
        )); 
    }

    if (isset($good['height'])) {
        $STH_tv->execute(array(
            'tmplvarid'=> 23, // высота растений
            'contentid'=> $productId,
            'value'=> $good['height'],
            'valueu'=> $good['height']
        )); 
    }

    if (isset($good['length'])) {
        $STH_tv->execute(array(
            'tmplvarid'=> 24, // длина
            'contentid'=> $productId,
            'value'=> $good['length'],
            'valueu'=> $good['length']
        )); 
    }

    if (isset($good['zerna'])) {
        $STH_tv->execute(array(
            'tmplvarid'=> 25, // число рядов зерен
            'contentid'=> $productId,
            'value'=> $good['zerna'],
            'valueu'=> $good['zerna']
        )); 
    }



    return $productId;

}



function getImageAndDescription($DBH) {
    $STH = $DBH->prepare("SELECT * FROM rusrostok__goods WHERE image is null AND description is null ORDER BY id ASC LIMIT 50"); //AND articul is null
    $STH->setFetchMode(PDO::FETCH_ASSOC);  
    $STHupd = $DBH->prepare("UPDATE rusrostok__goods SET image = :image , description = :description WHERE id = :id LIMIT 1"); //, articul = :articul
    
    $STHdel = $DBH->prepare("DELETE FROM  rusrostok__goods  WHERE id = :id LIMIT 1");
    $STH->execute(); 
    if($STH->rowCount() > 0)  {
        
        while($row = $STH->fetch()) { 
           $row['good'] = unserialize($row['good']);

            if ($imgAndDescr = loadImageAndDescription ($row['good']['href'] )) {
                try { 
                    $DBH->beginTransaction();
                    $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                    $STHupd->execute(array (
                        'image' => $imgAndDescr['image'],
                        'description' => ($imgAndDescr['description'] ? $imgAndDescr['description']: '-'),
                        'id' => $row['id']
                        )); 
                        //'articul' => $imgAndDescr['articul'],

                    $DBH->commit(); 
                } catch(PDOExecption $e) { 
                    $DBH->rollback(); 
                    print "Error!: " . $e->getMessage() . "</br>"; 
                    exit("Exiting");
                }
            }else {
                 $STHdel->execute(array (
                        'id' => $row['id']
                )); 
            }           
        }
        
        return false;
    }else {
        return true;
    }
}




function loadImageAndDescription ($url) {
    
    global $hostname;

    $result = array();

    $page = loadPage($hostname.$url);
   // print 'страница-'.$page;

    $document = phpQuery::newDocument($page);
    $container = getDOMChild('.node .content' , $document);
	//print 'контейнер'.$container;

    if (count($image = pq($container['.field-name-field-image .field-items .field-item img'])) > 0) {
        $linkToImg = $image->attr('src');
        $linkToImg = stristr($linkToImg, '?', true);
        $ext = @end (explode('.' , $linkToImg));

        $fullLinkToImg = $linkToImg;

        $newfile = '../assets/images/parser/'.md5($linkToImg).'.'.$ext;

        // $newfile = 'assets/images/parser/test.jpg';
        if(!file_exists($newfile)){
            copy($fullLinkToImg, $newfile);
        }

       // $result['image'] = curl_loadImage($linkToImg, $ext);
        
       $result['image'] = $newfile;


    }

    if (count(($description = pq($container['.field-name-body .field-items .field-item']))) > 0) {
        //$description->find('h2')->remove();
       // $description->find('a')->remove();
        $result['description'] = trim(iconv("UTF-8", "ISO-8859-1",  $description->html()));
        $result['description'] = ($result['description'] ? $result['description'] : '-');
    }

/*
    if (count(($articul = pq($container['.card-info ul.row li:last-child']))) > 0) {
    	$result['articul'] = trim($articul->text());
		$result['articul'] = str_replace("Артикул:", "", $result['articul']);
    }
    */

   // print_r($result);

    return $result;
    
}



function saveGoods($goods,$DBH) { 
    pre($goods);
    $STH = $DBH->prepare("INSERT INTO rusrostok__goods ( 
        good , 
        `timestamp`
        )
        values (
        :good,
        :timestampp
        )
        ON DUPLICATE KEY UPDATE good = :goodd , `timestamp` = :timestamppp" , 
        array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
    ); 

    try { 
        $DBH->beginTransaction();
        $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        foreach ($goods as $key => $value) {

        	$STH->execute(array(
	            'good' => serialize($value),
                'goodd' => serialize($value),
                'timestampp' => time(),
                'timestamppp' => time()
	        ));

        	/*
            $data = array( 
                'good' => serialize($value),
                'goodd' => serialize($value),
                'timestampp' => time(),
                'timestamppp' => time()
            );
            $STH->execute($data);
            */
        }
        $DBH->commit();
       
    } catch(PDOExecption $e) { 
        $DBH->rollback(); 
        print "Error!: " . $e->getMessage() . "</br>"; 
        exit("Exiting");
    }
    return true;


}


function insertCat($name,$DBH) {

     $STH_sc = $DBH->prepare("INSERT INTO rusrostok_site_content ( 
        pagetitle ,  
        alias ,  
        published, 
        parent,
        isfolder,
        template
        ) 
        values (
        :pagetitle ,  
        :alias ,  
        1, 
        67,
        1,
        7
        )"
    );  

    try { 
        $DBH->beginTransaction();
        $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $STH_sc->execute(array(
            'pagetitle' => $name, 
            'alias' => getAlias($DBH, $name),
        ));
        $catId = $DBH->lastInsertId();

    } catch(PDOExecption $e) { 
        $DBH->rollback(); 
        print "Error!: " . $e->getMessage() . "</br>"; 
        exit("Exiting");
    } 


    if (is_numeric($catId)) {
        $DBH->commit(); 
        return $catId;
    }else {
        $DBH->rollback(); 
        return false;
    }

}

function  getAlias($DBH , $txt){

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
    
    $STH = $DBH->prepare("SELECT id FROM rusrostok_site_content WHERE alias = :alias LIMIT 1"); //только здесь
    $STH->setFetchMode(PDO::FETCH_ASSOC);  

    do{
        $STH->execute(array('alias' => $alias )); 
        if($STH->rowCount() == 1) $alias .= rand(1, 9);
    }while(($STH->rowCount() == 1));
    if( ! $STH) $alias= false;
    return $alias;
}

function getParents(&$goods,$DBH) {
    $STH = $DBH->prepare("SELECT id FROM rusrostok_site_content WHERE parent = 67 AND isfolder = 1 AND pagetitle LIKE :name LIMIT 1");
    $STH->setFetchMode(PDO::FETCH_ASSOC);  
    
    if (is_array($goods) && count($goods)) {
        foreach ($goods as $key => $value) {
            if ($key !== 'thead' AND  $key !== 'cats') {
                $STH->execute(array('name' => $value['cats'] )); //словил тут ошибку
                if($STH->rowCount() == 1)  {
                    if($row = $STH->fetch()) { 
                        $goods[$key]["catsID"] = $row['id'];
                    }
                }else {
                    $goods[$key]["catsID"] = insertCat($value['cats'],$DBH); //и тут
                }
            }
        }
    }
}



function getProdsOnPage($data) {
    global $hostname;
    
    $linkListPaginate = array();
    $goods = array();
    $i = 0;
    $page = loadPage($hostname.$data['href']);
    $document = phpQuery::newDocument($page);
    $itrAll=0;

    $thead = getDOMChild('.view .view-content .views-table' , $document);
    $goods['thead'] = '<table><thead>'.iconv("UTF-8", "ISO-8859-1", pq($thead)->find('thead')->html()).'</thead>';
    $goods['cats'] = $data['name'];

    //обычный шаблон
    $elems = getDOMChild('.view-catalog-product .view-content .views-table tbody tr' , $document);
    if (count( pq($elems)) > 0) {
        foreach ($elems as $keyG => $valueG) {                    
            // $goods[$itrAll]['price'] = preg_replace('/[^0-9]/', '', pq($valueG)->find('.card-list__item-price')->text());
            $goods[$itrAll]['name'] = iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-title')->text());
            $goods[$itrAll]['cats'] = $data['name'];
            $goods[$itrAll]['href'] = pq($valueG)->find('.views-field-title a')->attr("href");

            $goods[$itrAll]['veg_period'] =iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-field-veg-period')->text()); //вегетационный период
            $goods[$itrAll]['ot_vusadki'] = pq($valueG)->find('.views-field-field-ot-vusadki')->text(); //от высадки рассады
            $goods[$itrAll]['weight'] = pq($valueG)->find('.views-field-field-ves-ploda')->text(); //вес
            $goods[$itrAll]['type'] = iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-field-tip')->text()); //тип
            $goods[$itrAll]['figure'] = iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-field-forma')->text()); //форма


            //$goods[$itrAll]['vendor'] = getVendorAndState(($goodsProps = pq($valueG)->find('.cl_char')) , 'производитель');
            //$goods[$itrAll]['state'] = getVendorAndState($goodsProps , 'наличие');              
            //$goods[$itrAll++]['params'] = getProps($goodsProps);
                
            $itrAll++;  
            //$goods[$keyPage]['name'] = pq($linkToPage)->text();
        }
    }   

    //шаблон для зелени
    $elems = getDOMChild('.view--catalog-product-3-zelen .view-content .views-table tbody tr' , $document);
    if (count( pq($elems)) > 0) {
        foreach ($elems as $keyG => $valueG) {                    
            $goods[$itrAll]['name'] = iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-title')->text());
            $goods[$itrAll]['cats'] = $data['name'];
            $goods[$itrAll]['href'] = pq($valueG)->find('.views-field-title a')->attr("href");

            $goods[$itrAll]['veg_period'] = iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-field-veg-period')->text()); //вегетационный период
            $goods[$itrAll]['dop_info'] = iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-field-dop-info')->text()); //дополнительная информация

            $itrAll++;  
        }
    }   

    //шаблон для кукурузы
    $elems = getDOMChild('.view-catalog-product-2-kukuruza .view-content .views-table tbody tr' , $document);
    if (count( pq($elems)) > 0) {
        foreach ($elems as $keyG => $valueG) {                    
            $goods[$itrAll]['name'] = iconv("UTF-8", "ISO-8859-1", pq($valueG)->find('.views-field-title')->text());
            $goods[$itrAll]['cats'] = $data['name'];
            $goods[$itrAll]['href'] = pq($valueG)->find('.views-field-title a')->attr("href");
            
            $goods[$itrAll]['after_vshodov'] = pq($valueG)->find('.views-field-field-posle-vshodov')->text(); //После всходов, дней
            $goods[$itrAll]['temperature'] = pq($valueG)->find('.views-field-field-activ-t')->text(); //Сумма активных температур
            $goods[$itrAll]['height'] = pq($valueG)->find('.views-field-field-vusota')->text(); //высота растений
            $goods[$itrAll]['length'] = pq($valueG)->find('.views-field-field-dlina')->text(); //длина 
            $goods[$itrAll]['zerna'] = pq($valueG)->find('.views-field-field-zerna')->text(); //число рядов зерен 
                
            $itrAll++;  
        }
    }   

    return $goods;
}




function getMenuArr($menu) {
    if ( ! $menu instanceof phpQueryObject) return false;
    $categories = array();
    if (count(($elem = $menu['a'])) > 0) {    
        $i = 0;
        foreach ($elem as $keyPage => $linkToPage) {
            $href = pq($linkToPage)->attr('href');
            $categories[$i]['href'] = $href;
            $categories[$i++]['name'] = iconv("UTF-8", "ISO-8859-1", pq($linkToPage)->text());
        }
    }
    return $categories;
}





// не правила

/*
function curl_loadImage($link, $extension , $path = false) {
    global $hostname;

    $confpath = '../assets/images/parser/';
    $confpathShort = '../assets/images/parser/';
    $config = array(
        'localPath' => $confpath
    );

    $localImgName = md5($link).'.'.mb_strtolower($extension , "UTF-8");
    $dir = substr($localImgName , 0 , 2).'/';
       
    if (!file_exists($config['localPath'].$dir)) { 
        @mkdir($config['localPath'].$dir , 0777, 1);
    }

    $localPath = $config['localPath'].$dir.$localImgName;

   // if (!file_exists($localPath)) { 
        $ch = curl_init($hostname.$link);
       // print $ch; die(); return;
        $fp = fopen($localPath, "w");
        curl_setopt($ch, CURLOPT_FILE, $fp);
     //   curl_setopt($ch, CURLOPT_HEADER, 0);
     //   curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt"); 
     //   curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt"); 
     //   curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
     //   curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
   // }

    return $confpathShort.$dir.$localImgName;
}
*/

function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function pre( $hentry) {

    if (!is_array($hentry)) return false;
    echo "<pre>";
    print_r($hentry);
    echo "</pre>";
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

function loadPage($link) {
    $curl = curl_init($link); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($curl, CURLOPT_COOKIEJAR, "cookie.txt"); 
    curl_setopt($curl, CURLOPT_COOKIEFILE, "cookie.txt"); 
    curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt ($curl, CURLOPT_TIMEOUT, 9999);
 
    $page = curl_exec($curl);
    return $page;
}

function getDOMChild($selector, $document ) {
    $hentry = $document->find($selector); 
    if ($hentry instanceof phpQueryObject) 
        return $hentry;
}




echo 'yep';
