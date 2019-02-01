<?php
/**
 * data_from_1c
 *
 * Выгрузка данных из 1С
 *
 * 1C:Exchange
 *
 * @version 2.6
 * @date    14.07.2017
 *
 *
 *
 *
 *
 *
 *
 *
 */

/*
	Статусы:
	from_1c  - файлы сессии выгружаются из 1С
	new      - файлы сессии выгружены из 1С
	to_db    - инфа из файлов сессии выгружается в базу данных сайта
	complete - выгрузка сессии завершена
 */
	
$modx->db->query("CREATE TABLE IF NOT EXISTS ".$modx->getFullTableName('_1c_log')." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dth` varchar(31) NOT NULL,
  `log` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

$modx->db->query("CREATE TABLE IF NOT EXISTS ".$modx->getFullTableName('_1c_')." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session` varchar(127) NOT NULL,
  `file` varchar(127) NOT NULL,
  `type` set('import','offers') NOT NULL DEFAULT 'import',
  `status` set('from_1c','new','to_db','complete','cancel') NOT NULL DEFAULT 'from_1c',
  `step` tinyint(4) NOT NULL DEFAULT '1',
  `point` int(11) NOT NULL DEFAULT '0',
  `dth` varchar(31) NOT NULL,
  `status_dth` varchar(31) NOT NULL,
  `cancel_dth` varchar(31) NOT NULL,
  `dt` bigint(20) NOT NULL,
  `status_dt` bigint(20) NOT NULL,
  `cancel_dt` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");


$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='data_from_1c::start'"); // LOG

$session_name= session_name();
$session_id= session_id();

if( ! $_SESSION['1c']['time']) $_SESSION['1c']['time']= date('Y-m-d-H-i-s');
$time= $_SESSION['1c']['time'];

$folder= '1c_exchange/xml/'.date('Y-m').'/';
if( ! file_exists(MODX_BASE_PATH.$folder)) mkdir(MODX_BASE_PATH.$folder, 0777, true);

$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::000'"); // LOG

//$modx->db->query("UPDATE ".$modx->getFullTableName('_1c_')." SET cancel_dth='".date('Y-m-d-H-i-s')."', cancel_dt=".time()."
//	WHERE session<>'{$session_id}' AND status<>'complete'");

if($_GET['type'] == 'catalog')
{
	if($_GET['mode'] == 'checkauth')
	{
		print "success\n";
		print $session_name ."\n";
		print $session_id ."\n";

		$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::checkauth::'"); // LOG
		
		
	}elseif($_GET['mode'] == 'init'){
		print "zip=no\n";
		print "file_limit=".(1024*1024*5)."\n";
		
		$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::init::'"); // LOG
		
		
	}elseif($_GET['mode'] == 'file' && ! empty($_GET['filename'])){
		$filename= trim($_GET['filename']);
		
		$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::001:: {$filename}'"); // LOG
		
		$rassh= substr($filename, strrpos($filename, '.'));
		
		$filetype= strpos($filename, 'offers')!==false ? 'offers' : 'import';
		
		if($rassh == '.xml') $filename= $time.'__'.$filename;
		
		$file= '';
		if($_SESSION['1c']['catalog_file'][$filename]['step'] != 2)
		{
			if($rassh == '.xml')
			{
				$file= $folder. $filename;
				
				$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::002:: {$file}'"); // LOG
				
				if( ! file_exists($file))
				{
					$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::003:: {$file}'"); // LOG
					
					$_SESSION['1c']['catalog_file'][$filename]['step']= 2;
					$_SESSION['1c']['catalog_file'][$filename]['filepath']= MODX_BASE_PATH. $file;
					
					$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_') ." SET file='".date('Y-m')."/{$filename}',
						type='{$filetype}', session='{$session_id}', status='from_1c', dth='".date('Y-m-d-H-i-s')."', dt=".time());
				}else{
					$file= '';
				}
				
			}elseif(true){
				$filename2= md5($filename) .$rassh;
				$fldr= 'x'.substr($filename2,0,2);
				
				$file= 'assets/';
				$file .= $rassh=='.txt' ? 'files' : 'images';
				$file .= '/1c/'.$fldr.'/';
				
				if( ! file_exists(MODX_BASE_PATH. $file)) mkdir(MODX_BASE_PATH. $file,0777,true);
				$file .= $filename2;
				if(file_exists(MODX_BASE_PATH. $file)) unlink(MODX_BASE_PATH. $file);
				$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::001-2:: {$file}'"); // LOG
				$_SESSION['1c']['catalog_file'][$filename]['step']= 2;
				$_SESSION['1c']['catalog_file'][$filename]['filepath']= MODX_BASE_PATH. $file;
			}
			
		}else{
			$file= $_SESSION['1c']['catalog_file'][$filename]['filepath'];
			
			$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::004:: {$file}'"); // LOG
		}
		
		if( ! empty($file))
		{
			$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::005:: {$file}'"); // LOG
			
			if($fp= fopen($file, "ab"))
			{
				$post_data= file_get_contents("php://input");
				
				if($post_data !== false && ! empty($post_data))
				{
					$byte= fwrite($fp, $post_data);
					
					if($byte)
					{
						$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::006:: {$file}'"); // LOG
						print "success\n";
					}
				}else{
					$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::007:: {$file}'"); // LOG
					print "failure\n";
				}
			}else{
				$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::008:: {$file}'"); // LOG
				print "failure\n";
			}
		}else{
			$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::009:: {$file}'"); // LOG
			print "failure\n";
		}
		
		
	}elseif($_GET['mode'] == 'import'){
		if($_SESSION['1c']['step'] != 3)
		{
			$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::import::'"); // LOG
			
			$_SESSION['1c']['step']= 3;
			
			$modx->db->query("UPDATE ". $modx->getFullTableName('_1c_') ." SET status='new', status_dth='".date('Y-m-d-H-i-s')."', status_dt=".time()."
				WHERE session='{$session_id}'");
			
			file_get_contents($modx->makeUrl(377,'','','full'));
		}
		
		print "success\n";
	}else{
		$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='catalog::010'"); // LOG
		print "failure\n";
	}
}else{
	$modx->db->query("INSERT INTO ".$modx->getFullTableName('_1c_log')." SET dth='".date('Y-m-d-H-i-s')."', log='011'"); // LOG
	print "failure\n";
}
