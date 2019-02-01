<div class="module_box">
    <!-- -------------------------- -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="/assets/modules/comments_mod/_script.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/modules/comments_mod/_styles.css" />

    <br>
    <div class="success" style="display: none;">&nbsp;</div>
    <br>
    <br>

    <button class="del_unpublished">Удалить неопубликованные отзывы</button>
    <button class="publish_all">Опубликовать все</button>
    <button class="unpublish_all">Снять с публикации все</button>

    <br>
    <br>



    <?php 
        
   
        
	$orders= $modx->db->query( "SELECT * FROM ". $modx->getFullTableName( '_comments_mod' ) ." ORDER BY date DESC LIMIT 200" );
	if( $orders && $modx->db->getRecordCount( $orders ))
	{
		$print .= '<p>Последние отзывы:</p>';
		
		$print .= '
			<table class="comments_table" cellpadding="0" cellspacing="0">
				<tr class="tit">
					
					<td class="name" valign="center">Имя</td>
					<td class="star" valign="center">Оценка</td>
					<td class="comment" valign="center">Отзыв</td>
					<td class="page" valign="center">Страница</td>
					<td class="date" valign="center">Дата</td>
					<td class="status" valign="center">Опубликован</td>
					
				</tr>';
         
		$ii= 0;	 
		while( $row= $modx->db->getRow( $orders ) )
		{  
			$ii++;
			$print .= '<tr class="item '.( $row[ 'active' ] == 0 ? '' : 'active' ).' ">
            
            
                    <td class="name" valign="center">'. $row[ 'name' ] .'</td>
					<td class="star" valign="center">'. $row[ 'stars' ] .'</td>
					<td class="comment" valign="center">'. $row[ 'comment' ] .'</td>
					<td class="page" valign="center"><a href="'.$modx->makeUrl( $row[ 'pageid' ], '', '', 'full' ).'" target="_blank">Перейти</a></td>
					<td class="date" valign="center">'. $row[ 'date' ] .'</td>
					<td class="status" valign="center">
                        <span class="change" data-publ="'.$row[ 'active' ].'" data-id="'.$row[ 'id' ].'">'. ( $row[ 'active' ] == 0 ? 'Опубликовать' : 'Снять с публикации' ) .'</span>
                    </td>';
            
            
			$print .= '</tr>';
		}
        $print .= '</table>';
	}

     $print .= '</div>';           


print $print;




?>