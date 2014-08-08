<?php
/* 
 * [BEGIN_COT_EXT]
 * Hooks=ajax
 * [END_COT_EXT]
 * */

/**
 * plugin txtusersexport for Cotonti Siena
 * 
 * @package txtusersexport
 * @version 1.0.0
 * @author esclkm
 * @copyright 
 * @license BSD
 *  */

defined('COT_CODE') or die('Wrong URL');


$file = cot_import('file','G','TXT');
if (!empty($file) && cot_auth('plug', 'txtusersexport', 'A'))
{
	require_once cot_incfile('users', 'module');
	
	$wheretype = cot_import('where','G','INT');
	$wheretype = (in_array($wheretype, array(1, 2, 3))) ? $wheretype : 1;

	$exfile = explode('.', $file);

	$mskin = cot_tplfile(array('txtusersexport', $file), 'plug');
	
	$file = $exfile[0];
	$extf = (!$exfile[1]) ? 'txt' : $exfile[1];
	
	$t1 = new XTemplate($mskin);

	$where['where'] = $cfg['plugin']['txtusersexport']['where'.$wheretype];
	$where = array_filter($where);
	$where = implode(" AND ", $where);
	$where = !empty($where) ? $where : '1';
	$sql2 = $db->query("SELECT * FROM $db_users WHERE  $where");
	$jj = 0;
	while($row2 = $sql2->fetch())
	{
		$jj++;

		$t1->assign(cot_generate_usertags($row2, 'EXP_ROW_', '', false, false));
		$t1->assign(array(
			'EXP_ROW_EMAIL' => $row2['user_email'],
			'EXP_ROW_ODDEVEN' => cot_build_oddeven($jj),
			'EXP_ROW_NUM' => $jj,
			'SEP' => '[[br]]',
		));/**/
			
		$t1->parse('MAIN.ROW');
	}

	$t1->parse('MAIN');
	$xtext = $t1->text('MAIN');
//	$xtext = iconv("UTF-8","WINDOWS-1251//IGNORE",$xtext);
	if(!empty($cfg['plugin']['txtusersexport']['replacefrom']) && !empty($cfg['plugin']['txtusersexport']['replaceto']))
	{
		$umlaut_array = explode(",", $cfg['plugin']['txtusersexport']['replacefrom']);
		$umlaut_replace   = explode(",", $cfg['plugin']['txtusersexport']['replaceto']);
		$xtext = str_replace($umlaut_array, $umlaut_replace, $xtext);
	}
	
	if(!empty($cfg['plugin']['txtusersexport']['encoding']))
	{	
		$xtext = mb_convert_encoding($xtext, $cfg['plugin']['txtusersexport']['encoding']);
	}
	
	$xtext = str_replace('[[br]]', "\r\n" , $xtext);
	file_put_contents($cfg['plugin']['txtusersexport']['folder'].'/'.$file.'.'.$extf, $xtext);

	if(file_exists($cfg['plugin']['txtusersexport']['folder'].'/'.$file.'.'.$extf))
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public", false);
		header("Content-Description: File Transfer");
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment;filename="'.$file.date("_Ymd-Hi").'.'.$extf.'"');
		header("Content-Transfer-Encoding: binary");
		header('Content-Length: ' . filesize($cfg['plugin']['txtusersexport']['folder'].'/'.$file.'.'.$extf));
		ob_clean();
		flush();
		readfile($cfg['plugin']['txtusersexport']['folder'].'/'.$file.'.'.$extf);
		exit;
	}
}
?>