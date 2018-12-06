<?php
// Sample Main File V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
use Venus\library\core as Core;
use Venus\library\t3rdparty as ThirdParty;

/***  AutoLoader  ***/
require_once(dirname(__FILE__) . '/library/core/AutoLoader.php');
$Loader = new Core\AutoLoader(true);

/***  Objects  ***/
$Config = new Core\Config('config/config.php');
$ErrorHandler = new Core\ErrorHandler();
$Listener = new Core\Listener();
$Template = new Core\Template();
$Locale = new Core\Locale('storage/language');
$Plugin = new Core\Plugin('plugin');
$File = new Core\File();

/***  Usage(Router)  ***/
$Listener->Bind('GET','/',$Listener->BoilCallBack(array($Template,'ShowFile'),'view/'.$Locale->GetSlangDirection('DEFAULT').'/intro.html',array('version'=>$File->Read('version')),false,false));
$Listener->Bind('GET','/',$Listener->BoilCallBack(array($Template,'ShowFile'),'view/'.$Locale->GetSlangDirection('DEFAULT').'/intro.html',array('version'=>$File->Read('version')),false,false));
$Listener->Bind('POST','/ajax',
	function($input)
	{
		$Elektra = new ThirdParty\Elektra();
		$Elektra->Add('#form_result','success','html')->Response(true);
	});
?>
