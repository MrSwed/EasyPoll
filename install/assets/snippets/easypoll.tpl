//<?php
/**
 * EasyPoll
 * 
 * Another Poll Module, inspired by the Poll Module developped by garryn
 *
 * @category 	snippet
 * @version 	0.3.3
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category Content
 * @internal    @installset base, sample
 */

// set up parameters
$config = array();
$config['lang']				= isset($lang) && preg_match('/^[a-z]{2,3}$/', $lang) ? $lang : 'en';
$config['easylang']			= isset($easylang) && preg_match('/^[a-z]{1,3}$/', $easylang) ? $easylang : $config['lang'];
$config['pollid']			= isset($pollid) ? intval($pollid) : false;
$config['onevote']			= isset($onevote) ? $onevote == true : false;
$config['useip']			= isset($useip) ? $useip == true : false;
$config['nojs']				= isset($nojs) ? $nojs == true : false;
$config['noajax']			= isset($noajax) ? $noajax == true : false;
$config['archive']			= isset($archive) ? $archive == true : false;
$config['votesorting']		= isset($votesorting) && preg_match('/^(Sorting|Votes)(\s(DESC|ASC))?$/i', $votesorting) ? $votesorting : 'Sorting ASC';
$config['skipfirst']		= isset($skipfirst) ? $skipfirst == true : false;
$config['css']				= isset($css) ? $css : false;
$config['identifier']		= isset($identifier) ? $identifier : 'easypoll';
$config['accuracy']			= isset($accuracy) ? intval($accuracy) : 1;
$config['tplVoteOuter']		= isset($tplVoteOuter) ? $tplVoteOuter : false;
$config['tplVote']			= isset($tplVote) ? $tplVote : false;
$config['tplResultOuter']	= isset($tplResultOuter) ? $tplResultOuter : false;
$config['tplResult']		= isset($tplResult) ? $tplResult : false;
$config['ovtime']			= isset($ovtime) ? intval($ovtime) : 608400;
$config['jscallback']		= isset($jscallback) ? $jscallback : false;
$config['customjs']			= isset($customjs) ? $customjs : false;
$config['showexception']	= isset($showexception) ? $showexception == true : false;

// set the base path
$path = $modx->config['base_path'] . 'assets/snippets/EasyPoll/';

// check if required files exist
$langfile = $path . 'lang/lang.' . $config['lang'] . '.php';
$classfile = $path . 'easypoll.class.php';

if(!file_exists($langfile)){
	$modx->messageQuit('EasyPoll Snippet Error: Unable to locate language File for language: ' . $config['lang']);
	return;
}

if(!file_exists($classfile)){
	$modx->messageQuit('EasyPoll Snippet Error: Unable to locate easypoll.class.php');
	return;
}

// include files
$_lang = array();
require($langfile);
require_once($classfile);

try {
	$handler = new EasyPoll($config, $_lang);
	return $handler->generateOutput();
} catch (Exception $ex){
	// only display the exception if we have a error code above 0. otherwise remain silent
	if($ex->getCode() > 0 || $config['showexception']){
		// if we get a code above or equal to 128, we just exit
		if($ex->getCode() >= 128){
			return $ex->getMessage();
		} else {
			$modx->messageQuit('EasyPoll Snippet Error: ' . $ex->getMessage());
		}
	}
}

return;
?>