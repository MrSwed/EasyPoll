// <?php 
/**
 * EasyPoll
 * 
 * inspired by the Poll Module developed by garryn
 * 
 * @category	module
 * @version 	0.3.3
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties
 * @internal	@guid docman435243542tf542t5t	
 * @internal	@shareparams 1
 * @internal	@dependencies requires files located at /assets/modules/EasyPoll/
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base, sample
 */


/**
 * ------------------------------------------------------------------------------
 * Easy Poll Module for MODx
 * ------------------------------------------------------------------------------
 * Easy Poll Voting, inspired by the Poll Module developed by garryn
 *
 * This Module allows creation of multiple Polls, localized for different languages.
 * Polls can be set active/inactive and timed with a start and end date.
 *
 * ------------------------------------------------------------------------------
 * Read the bundled documentation.html for usage instructions.
 * ------------------------------------------------------------------------------
 *
 * The EasyPoll Module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * EasyPoll is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EasyPoll (located in "/snippets/EasyPoll/"); if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 * or visit: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Dependencies:
 * MODx 0.9.5, 0.9.6 (this is the version this module was developped for. might work
 *             with other versions as well. not tested)
 * PHP Version 5 or higher
 * MySQL Version 4.1 or higher (utf-8, InnoDB, Trasactions)
 *
 * @author banal
 * @version 0.3 <2008-02-08>
 */
if(!isset($modx)) die();

$basePath = $modx->config['base_path'];
$easyPollPath = $basePath . 'assets/modules/EasyPoll/';

// ------------------------------------------------------------------------------
// get the user language settings
// ------------------------------------------------------------------------------
$manager_language = $modx->config['manager_language'];
$sql =	'SELECT setting_name, setting_value FROM ' . $modx->getFullTableName('user_settings') .
		' WHERE setting_name=\'manager_language\' AND user=' . $modx->getLoginUserID();
$rs = $modx->db->query($sql);

if ($modx->db->getRecordCount($rs) > 0) {
    $row = $modx->db->getRow($rs);
    $manager_language = $row['setting_value'];
}

// load localization file.
$_lang = array();
include_once($easyPollPath . 'lang/english.inc.php');

if($manager_language != 'english') {
	$langfile = $easyPollPath . 'lang/' . $manager_language . '.inc.php';
	if(file_exists($langfile)) {
	     include_once $langfile;
	}
}

// ------------------------------------------------------------------------------
// Create EasyPollController
// ------------------------------------------------------------------------------
$classfile = $easyPollPath . 'include/EasyPollController.class.php';
if(!file_exists($classfile))
	$modx->messageQuit(sprintf($_lang['EP_noclassfile'], $classfile));

require_once($classfile);
$controller = new EasyPollController($_lang, $easyPollPath);
try {
	$controller->run();
} catch (Exception $ex){
	$modx->messageQuit($ex->getMessage());
}
return;
