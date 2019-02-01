<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$result ="";

// ОТКРЫТЬ СНИППЕТ SuoerPuperForms НА САЙТЕ И СКОПИРОВАТЬ ДАННЫЕ ОТТУДА 
// 
//КОМУ (через запятую)
$mailto = 'galina.it@delta-ltd.ru';
//ОТ (если SMTP, то это поле - логин)
$mailfrom= 'info.arttrus@yandex.ru';
//Пароль от почты (если SMTP)
$mailpassw= 'dfger43fv';
//Сервер SMTP (если SMTP)
$smtp= 'smtp.yandex.ru';
//Порт SMTP (если SMTP)
$smtpport= 465;	

// СМОТРИ СТРОКУ 7
// ЕСЛИ ОТПРАВКА НЕ ПОЛУЧАЕТСЯ, СПРОСИТЬ У МЕНЯ 

$modx->db->query("CREATE TABLE IF NOT EXISTS ".$modx->getFullTableName('_comments_mod')." (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `pageid` int(11) NOT NULL,
		  `name` varchar(63) NOT NULL,
		  `comment` varchar(1024) NOT NULL,
		  `stars` varchar(63) NOT NULL,
		  `date` varchar(63) NOT NULL,
		  `active` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
	

if ($act == "sendComment") {	


		$pageid= intval( $_POST[ 'pageid' ] );
		$name= addslashes( trim( $_POST[ 'name' ] ) );
		$comment= addslashes( trim( $_POST[ 'message' ] ) );
		$stars= intval( trim( $_POST[ 'numstar' ] ) );
		$date = date("d.m.Y H:i");

		$sql = 'INSERT INTO '.$modx->getFullTableName('_comments_mod').' SET
							pageid='.$pageid.',
							name="'.$name.'",
							comment="'.$comment.'",
							stars='.$stars.',
							date="'.$date.'",
							active=0';

		include_once( MODX_MANAGER_PATH .'includes/controls/class.phpmailer.php' );
	
		$message = "На сайте оставлен новый отзыв.<br> Для того, чтобы отзыв появился на сайте, нужно пройти модерацию в системе управления. <br> <br>";
		$message .= '<b>Имя</b>: '.$name.' <br> <b>Отзыв</b>: '.$comment;

		$phpmailer= new PHPMailer();

		if( false )
		{
			$phpmailer->SMTPDebug= 2;
			$phpmailer->Debugoutput = 'html';
		}
		if( $mailtype == 'smtp' )
		{
			$phpmailer->isSMTP();
			$phpmailer->Host= $smtp;
			$phpmailer->Port= $smtpport;
			$phpmailer->SMTPAuth= true;
			$phpmailer->SMTPSecure= 'ssl';
			$phpmailer->Username= $mailfrom;
			$phpmailer->Password= $mailpassw;
		}
		$phpmailer->CharSet= 'utf-8';
		$phpmailer->From= $mailfrom;
		$phpmailer->FromName= "Письмо с сайта";
		$phpmailer->isHTML( true );
		$phpmailer->Subject= 'Новый отзыв';
		$phpmailer->Body= $message;
		$mailto= explode( ',', $mailto ); foreach( $mailto AS $row ) $phpmailer->addAddress( trim( $row ) );
		$phpmailer->send();

		$modx->db->query($sql);

		return '{"sql":"'.$sql.'"}';

	}


if ($act == "printComment" ) {

	//$tpl = ''; //Шаблон вывода комментария

	$result= $modx->runSnippet('DocLister', array(
		'controller' => 'onetable',
		'idType' => 'documents',
		'table' => '_comments_mod',
		'idField' => 'id',
		'parentField' => 'pageid',
		'idType' => 'parents',
		'tpl' => $tpl,
		'sortBy'=>'date',
		'addWhereList'=>'active=1',
		'sortDir'=>'DESC'
	));
	
	return $result;
		
}

if ($act == "stars" ) {

	for ($i = 1; $i <= 5; $i++) {		
		$result .= '<div class="star '.($i <= $star ? "red" : "" ).'"></div>';
	}
	print $result;	
}

return;

//action="<?= $modx->makeUrl( $modx->documentIdentifier ) ? >"
