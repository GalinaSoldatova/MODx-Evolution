<?php
//можно добавить действие, и вообще менять вывод
// пример вызова
// [!ParseMultiParams? &act=`` &catparam=`[*cat_har*]`!] 
$params = explode( "||", $catparam);

if ($act == 'действие1') {

	if( $params )
	{ 
		foreach( $params AS $param )
		{ 
			$print .='<div>';
			$param= explode( "::", $param );
			if( ! empty( $param[ 0 ] ) || ! empty( $param[ 1 ] ) )
				$print .= '<span>'. $param[ 0 ] .': &nbsp; </span><b>'. $param[ 1 ] .'</b>';
			//	<img class="gal-img" src="'. $modx->runSnippet( 'ImgCrop45', array( 'img' => $param[0], 'w' => 200, 'h' => 150, ) ) .'"  />
			$print .='</div>';
		}
	}

}

if ($act == 'действие2') {
	
	if( $params )
	{ 
		foreach( $params AS $param )
		{ 
			$print .='<div>';
			$param= explode( "::", $param );
			if( ! empty( $param[ 0 ] ) || ! empty( $param[ 1 ] ) )
				$print .= '<span>'. $param[ 0 ] .': &nbsp; </span><b>'. $param[ 1 ] .'</b>';
			//	<img class="gal-img" src="'. $modx->runSnippet( 'ImgCrop45', array( 'img' => $param[0], 'w' => 200, 'h' => 150, ) ) .'"  />
			$print .='</div>';
		}
	}

}

	return $print;
