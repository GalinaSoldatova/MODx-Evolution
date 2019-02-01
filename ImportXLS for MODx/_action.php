<?php
//v02   
//=====================================================================================
$sm_base= '../assets/modules/importXLS/';
$module_url= MODX_MANAGER_URL .'?a='. $_GET[ 'a' ] .'&id='. $_GET[ 'id' ];


/********CONFIG**********/
$template = 4;
$catRoot = 2;


//$excludeTV =  array(18,12,4,13,11,10,5,9,1,14,15, 23, 8 );


/********CONFIG**********/


/*

$addExSql = '';
foreach ($excludeTV as $val){
     $addExSql .= ' AND tvnames.id <> '.$val.' ';
}
*/



$sql= "SELECT tvnames.id,  tvnames.name,  tvnames.caption FROM ".$modx->getFullTableName('site_tmplvar_templates')." AS tvtemplate
INNER JOIN  ".$modx->getFullTableName('site_tmplvars')." AS tvnames ON tvtemplate.tmplvarid  = tvnames.id 
WHERE tvtemplate.templateid = {$template} ".$addExSql." ORDER BY tvnames.caption ASC
LIMIT 100";


$symmaryTV = '';
$i =2;



$symmaryTV .= '<select class="pxselectlistdefaulttv" style="display: none">';
$symmaryTV .= '<option class="" data-type="NULL"  data-tvid="NULL">Не используется</option>';
$symmaryTV .= '<option class="" data-type="tv"  data-tvid="PGT">Название (PGT)</option>';
$symmaryTV .= '<option class="" data-type="tv"  data-tvid="CONTENT">Опиcание</option>';
if ($modx->db->getRecordCount($result = $modx->db->query($sql)) > 0) {
    echo $modx->db->getLastError();
    while ($row = $modx->db->getRow($result)) {
        if ($i > 13) $i =0;
        $symmaryTV .= '<option class="" data-type="tv" data-tvid="'.$row['id'].'">'.$row['caption'].'</option>';
        $i++;
    }
}




// ------------ фильтры -- -------------  

/*
$sql= "SELECT id,  type, name FROM ".$modx->getFullTableName('_catfilter')." 
WHERE e = 'y' AND type <> 'price' ORDER BY name ASC
LIMIT 100";


//$symmaryFILTER .= '<select class="pxselectlistdefaulttv">';

if ($modx->db->getRecordCount($result = $modx->db->query($sql)) > 0) {

    echo $modx->db->getLastError();
    while ($row = $modx->db->getRow($result)) {
        if ($i > 13) $i =0;
       // $symmaryFILTER .= '<option class=""  data-fltid="'.$row['id'].'">'.$row['name'].'<span> '.$row['type'].' ('.$row['id'].')</span></div>';
        $symmaryTV .= '<option class="" data-type="flt"  data-fltid="'.$row['id'].'">'.$row['name'].'</div>';
        $i++;
    }
}

*/



$symmaryTV .= '</select>';








if ($result = $modx->db->query('SHOW TABLES LIKE "%_site_content_redo_%"')){
    if($modx->db->getRecordCount($result)) { 
          $dooReDo  = '<div class="dooRedo">Откатить базу на шаг</div>';
    }
}





?>




<link rel="stylesheet" type="text/css" href="<?php print $sm_base;?>_styles.css" />
<script type="text/javascript" src="//yandex.st/jquery/2.1.0/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php print $sm_base;?>_script.js"></script>






<div class="darkBG">
     <div class="treeList"></div>
     <div class="modalNotice">
          <div class="titNotice">Подтвердите</div>
          <div class="oneSring">
              
          </div>
          <div class="buttonsAcc">
              <div class="accDelete">Удалить</div>
              <div class="accCancel">Отмена</div>
          </div>

          <!-- <div class="oneSring"> -->
               <!-- Обновлено: <span id="noticeUpl_upd"></span> -->
          <!-- </div> -->
          <!-- <div class="oneSring"> -->
               <!-- Создано разделов: <span id="noticeUpl_cpath"></span> -->
          <!-- </div> -->
          
     </div>
</div>

<div class="leftBL">
     <div class="processImport">Импорт</div>
     <form action="<?= $module_url ?>" enctype="multipart/form-data" method="post">
          <label class="file_upload">
               <span class="button">Выбрать</span>
               <mark>Файл не выбран</mark>
               <input type="file"  name="xlsFile" id="uploaded_file" multiple="false" accept=".xlsx,.xls*,.ods,.txt,.csv">
          </label>                              
          <div class="progress-bar orange shine">
               <span id="docprogress" style="width: 0%"></span>
          </div>    
                                           
     </form>
     


<!--     <div class="tabs"><div class="enTv active">TV параметры</div><div class="enFlt">Параметры фильтров</div></div>-->
<!--     <div class="clr"></div>-->
<!--     <div class="tvLists active">--><?//= $symmaryTV ?><!--</div>-->
<!--     <div class="filterLists">--><?//= $symmaryFILTER ?><!--</div>-->

</div>


<?= $symmaryTV ?>

<div class="rightBL">
     <div id="dropZone">
          <div class="textInDrop">Перетащите фото сюда</div>
     </div>
     
     <div class="fpwrap">
          <div class="fullImageProgress orange shine"><span id="progressFillImg" style="width: 0%"></span></div>
     </div>
     
</div>





<div class="controlAddsButton">
     <div class="dopBtn selectAcolsImg">Сопоставление картинок<span class="miniAddsTx">Выбрать столбец</span></div>
     <div class="dopBtn selectAunique">Уникальные значения<span class="miniAddsTx">По умолчанию PAGETITLE</span></div>
     
     <div class="dopBtn selectAcat">
          <input class="checkbox_px" type="checkbox"  disabled id="px_f_1">
          <label for="px_f_1"></label>
          Категория
          <span class="miniAddsTx">Не задана</span>
     </div>
     
     <div class="dopBtn selectAcolCat1st">
          <input class="checkbox_px" type="checkbox"  disabled id="px_f_2">
          <label for="px_f_2"></label>
          <span class="px_tesxCol">Столбцы с категориями</span>
          <span class="miniAddsTx">Не заданы</span>
     </div>


     <div class="dopBtn selectAcolLonkImg">
          <input class="checkbox_px" type="checkbox"  disabled id="px_f_3">
          <label for="px_f_3"></label>
          <span class="px_tesxCol2">Ссылки на картинки (URL)</span>
          <span class="miniAddsTx">Не заданы</span>
     </div>



     <div class="dopBtn selectAcolLocalImg">
          <input class="checkbox_px" type="checkbox"  disabled id="px_f_4">
          <label for="px_f_4"></label>
          <span class="px_tesxCol3">Пути к картинкам (LOC)</span>
          <span class="miniAddsTx">Не заданы</span>
     </div>

    
    

     <?= $dooReDo ?>



 <?= $vendors ?>

     <!-- <input type="text" name="delpo" id="postav" placeholder="Введите наименование поставщика"> -->
     <!-- <div class="dopBtn delNotUpdated">Удалить позиции, отсутствующие в текущем прайсе<span class="noticeDelepeItems"></span></div> -->
     <div class="infoArea"><img src="<?php print $sm_base?>tail-spin.svg" width="30"/><span>Jdfgfd</span>

    
 
     </div>
     <div class="clr"></div>
     
     
</div>

<div class="clr"></div>



<div class="prepaireTable"></div>
<div class="sheetList"></div>





 
<?php


function initF($imgPath) {
     if (strlen($imgPath) < 1) return false;
     //print_r($_FILES);
     if ($_FILES['xlsFile']) {
          $ext = end(explode('.',$_FILES['xlsFile']['name']));
          
          if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/xls/')) {
               echo 'rr';
               if (!mkdir($_SERVER['DOCUMENT_ROOT'].'/xls/')) {
                    echo 'fallse';
                    //return false;
               }else {
                    echo 'mkdirOk';
               }
          }  
           
          if (($ext != 'xls' || $ext != 'xlsx' || $ext != 'ods') && $_FILES['xlsFile']['size'] > 0 && $_FILES['xlsFile']['error'] == 0)   {
               $newfileName = time().'.'.$ext;
               if (move_uploaded_file($_FILES['xlsFile']['tmp_name'],'../xls/'.$newfileName)) {
                    return $_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileName;
               }
          }
     }
     return false;
}






function getArrValues($pathToXLS) {
     
     require_once($_SERVER['DOCUMENT_ROOT'].'/assets/modules/importXLS/Classes/PHPExcel.php');
     $objPHPExcel = PHPExcel_IOFactory::load($pathToXLS);
     $objPHPExcel->setActiveSheetIndex(0);
     $aSheet = $objPHPExcel->getActiveSheet();
     
     $nColumn = PHPExcel_Cell::columnIndexFromString($aSheet->getHighestColumn());
     $nColumn = 32;
     $nRow = ($aSheet->getHighestRow());
     
     $resultArr = array();
     $resultRowArr = array();
     
     for ($iterRow = 2; $iterRow <= $nRow; $iterRow++) {
          for ($iterCol = 0; $iterCol < $nColumn; $iterCol++) {
               $tmp = addslashes($aSheet->getCellByColumnAndRow($iterCol, $iterRow)->getCalculatedValue());
               //array_push($resultRowArr, iconv('utf-8', 'cp1251', $tmp));
               array_push($resultRowArr, $tmp);
          }
          array_push($resultArr,  $resultRowArr);
          $resultRowArr = array();
     }
     return $resultArr;
}








function GenerAlias($txt)
{
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
	
	do{
		$rr= $modx->db->query("SELECT id FROM `atflot_site_content` WHERE alias='{$alias}' LIMIT 1");
		if($rr && $modx->db->getRecordCount($rr)==1) $alias .= rand(1, 9);
	}while(($rr && $modx->db->getRecordCount($rr)==1) || ! $rr);
	if( ! $rr) $alias= false;
	
	return $alias;
}


function clearCache()
{
	global $modx;
	$modx->clearCache();
	include_once MODX_BASE_PATH . 'manager/processors/cache_sync.class.processor.php';
	$sync= new synccache();
	$sync->setCachepath( MODX_BASE_PATH . "assets/cache/" );
	$sync->setReport( false );
	$sync->emptyCache();
}
?>