<?php

/*
----- ATTENTION ------
!!!!!!! необходимы тесты 1!!!!!!!!!!
 */
//smtp//mail//default//
$mailtype= 'smtp';
//КОМУ (через запятую)
$mailto= 'galina.it@delta-ltd.ru';
//Видимые копии (через запятую)
$mailcc= false;
//Скрытые копии (через запятую)
$mailbcc= false;
//ОТ (если SMTP, то это поле - логин)
$mailfrom= '';
//Пароль от почты (если SMTP)
$mailpassw= '';
//Сервер SMTP (если SMTP)
$smtp= 'smtp.yandex.ru';
//Порт SMTP (если SMTP)
$smtpport= 465;

$filetypes= '/.png/.jpg/.jpeg/.gif/.svg';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include_once( MODX_MANAGER_PATH .'includes/controls/class.phpmailer.php' );


//error_reporting (E_ALL); // включаем вывод всех ошибок, а перед запуском скрипта в использование не забываем отключить!

if ($_GET['act']) $act = $_GET['act']; else $act='printForm';

if ($act == 'printForm') {
	
	$print .= '
		<div class="result"></div>
		<form id="priemnaya-form" action="'.$modx->makeUrl( $modx->documentIdentifier ).'?act=sendMail" method="POST" enctype="multipart/form-data" >
			<div class="top-bl">
				<div class="input setdefaultvalue defaultvalue left">
					<label for="formname" class="formname">ФИО</label>
					<input type="text" name="name" id="formname">
				</div>
				<div class="input setdefaultvalue defaultvalue right">
					<label for="formphone" class="formphone">Номер телефона</label>
					<input type="tel" name="phone" id="formphone" class="phonemask">
				</div>
			</div>
			<div class="bot-bl">
				<div class="input setdefaultvalue defaultvalue">
					<label for="formtext" class="formtext">Введите Ваше сообщение</label>
					<textarea class="form_elem" name="text"></textarea>
				</div>
			</div>
			<div class="clr"></div>
			Загрузите фото: <br>
			<div class="file-upload">
				<input type="file" accept="image/*" multiple style="display:block;" name="file[]" id="uploaded-file" >
			</div>


			<div class="clr"></div>

			<div class="private">
				Нажимая кнопку «Отправить» вы даете согласие на обработку персональных данных <br> в соответствии с <a target="_blank" href="[~22~]">политикой конфиденциальности</a>
			</div>
			<div class="clr">&nbsp;</div>
			<input type="submit" class="submit">
			<div class="clr">&nbsp;</div>
		</form>';


	print($print);
} 


if ($act == 'sendMail') {

	if(!empty($_POST))
	{
		$spfs_name= addslashes( trim( $_POST[ 'name' ] ) );
		$spfs_phone= addslashes( trim( $_POST[ 'phone' ] ) );
		$spfs_message= addslashes( trim( $_POST[ 'text' ] ) );

		$files= $_FILES['file'];
		
		for ($i=0; $i< count($files['name']); $i++)
		{
			//print_r($spfs_file);
			if($files['tmp_name'][$i])
			{
				$photoflag= true;
				$photoflag_error= true;
					$rassh= substr($files['name'][$i],strrpos($files['name'][$i],'.'));
					if(strpos($filetypes,'/'.$rassh.'/')!==false || $files['type'][$i]=='application/x-zip-compressed')
					{
						if($files['size'][$i] < 1024*1024*10) //10 Мб
						{
							$ip= $_SERVER['REMOTE_ADDR'];
							$folder= 'assets/images/superpuperforms/'.$ip.'/';
							srand(time());
							$file= md5($files['name'][$i].rand(10,99)).$rassh;
							if( ! file_exists(MODX_BASE_PATH.$folder)) mkdir(MODX_BASE_PATH.$folder, 0777, true);
							if(move_uploaded_file($files['tmp_name'][$i], MODX_BASE_PATH.$folder.$file))
							{	
								$index = $i+1;
								$mess .= '<p><a href="http://'.$_SERVER['HTTP_HOST'].'/'.$folder.$file.'">Приложение '.$index.'</a></p>';
								$photoflag_error= false;
							}else $result= '{"result":"error","text":"Ошибка загрузки файла! 002"}';
						}else $result= '{"result":"error","text":"Слишком большой файл! Больше 10Мб."}';
					}else $result= '{"result":"error","text":"Неверное расширение файла!"}';
			}
		}
		
		if( ! $spfs_phone ){
			$result= '{"result":"error","text":"Необходимо указать контактные данные!"}';
		}
		
		print $result;
		if( ! $result )
		{

			$mailtext = '<h3>Вам пришло письмо с сайта : <b>'.$tit.'</b></h3>'; //заголовок
			if(!empty($spfs_name)) $mailtext .= '<p>Имя: '.$spfs_name.'</p>'; 
			if(!empty($spfs_phone)) $mailtext .= '<p>Телефон: '.$spfs_phone.'</p>'; 
			if(!empty($spfs_message)) $mailtext .= '<p>Сообщение: '.$spfs_message.'</p>'; 
			if( $files ) $mailtext .= $mess;
			$mailtext .= '<p>Дата и время сообщения: '. date( 'd.m.Y - H:i' ) .'</p>';

			// отправляем email средствами php
			if( $mailtype == 'smtp' || $mailtype == 'mail' )
			{
				$subject = 'Письмо с сайта';
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
				$phpmailer->FromName= "";
				$phpmailer->isHTML( true );
				$phpmailer->Subject= $subject;
				$phpmailer->Body= $mailtext;
				$mailto= explode( ',', $mailto ); foreach( $mailto AS $row ) $phpmailer->addAddress( trim( $row ) );
				if( $mailcc ){ $mailcc= explode( ',', $mailcc ); foreach( $mailcc AS $row ) $phpmailer->addCC( trim( $row ) ); }
				if( $mailbcc ){ $mailbcc= explode( ',', $mailbcc ); foreach( $mailbcc AS $row ) $phpmailer->addBCC( trim( $row ) ); }
				$phpmailer_result= $phpmailer->send();

			}else{
				$phpmailer_result = mail($mailto, $subject, $message,
							 "From: ".$name." <".$email.">\r\n"
							 ."Reply-To: ".$email."\r\n"
							 ."X-Mailer: PHP/" . phpversion());
			}

			if($phpmailer_result) echo 'Заявка отправлена'; else echo 'Заявка НЕ отправлена. Обратитесь к администратору'; 
	
		} else {
			echo 'Вернитесь назад и проверьте все ли поля Вы ввели '; 
		}


	} //else echo 'пусто'; 

}
