<?php
$prefix = $modx->getFullTableName("site_tmplvar_contentvalues");
$ids = $modx->db->query("SELECT contentid FROM ".$prefix." WHERE tmplvarid =".$tmplvarid); //tmplvarid - id нужного tv-параметра с чекбоксом


while( $row = $modx->db->getRow($ids) ) {  
    print "[[DocLister? &tpl=`".$tpl."` &idType=`documents` &tvPrefix=``  &tvList=`".$tvList."` &documents=`". $row['contentid'] ."`]]" ;
}


//вызов - [!hits? &tpl=`TplCat` &tvList=`cat-img`  &tmplvarid=`12` !]