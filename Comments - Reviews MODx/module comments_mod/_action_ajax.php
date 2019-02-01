<?php



define('MODX_API_MODE', true);
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
startCMSSession(); 
$modx->minParserPasses=2;



if( $_GET[ 'act' ] == 'del_unpublished' )
{
	$orderinfo = $modx->db->query( "DELETE FROM ". $modx->getFullTableName( '_comments_mod' ) ." WHERE active=0" );
}
        
if( $_GET[ 'act' ] == 'publish_all' )
{
    $sql = "UPDATE ". $modx->getFullTableName( '_comments_mod' ) ." SET active=1 " ;
    $modx->db->query( $sql );
}
        
if( $_GET[ 'act' ] == 'unpublish_all' )
{
	$orderinfo = $modx->db->query( "UPDATE ". $modx->getFullTableName( '_comments_mod' ) ." SET active=0 " );
}
            
if( $_GET[ 'act' ] == 'change_status' )
{
    $new_published = $_GET[ 'publ' ];
    $id = $_GET[ 'id' ];
	$orderinfo = $modx->db->query( "UPDATE ". $modx->getFullTableName( '_comments_mod' ) ." SET active=".$new_published." WHERE id=".$id);
}
     
  