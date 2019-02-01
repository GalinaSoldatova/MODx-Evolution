<?php
if ($_GET['act'] == 'sendComment'){
    $result= $modx->runSnippet('Comments', array('act' => 'sendComment'));
    return $result; 
}
