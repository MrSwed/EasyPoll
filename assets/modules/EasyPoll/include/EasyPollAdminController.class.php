<?php
/**
 * ------------------------------------------------------------------------------
 * Easy Poll Admin Controller
 * ------------------------------------------------------------------------------
 * Easy Poll Admin Controller Class
 * This class encapsulates the presentation of the Easy Poll Administrative tasks.
 * It is responsible for HTML output and capturing user input. Logic should be
 * delegated to the EasyPollManager
 *
 * Dependencies/Requirements:
 * - EasyPollManager Class
 * - EasyPollController Class
 * - MODx 0.9.5, 0.9.6 ... others to be tested
 * - PHP Version 5 or greater
 * - MySQL Version 4.1 or better
 *
 * @author banal
 * @version 0.1 <2008-02-04>
 */

require_once(dirname(__FILE__) . '/EasyPollController.class.php');
require_once(dirname(__FILE__) . '/EasyPollManager.class.php');

class EasyPollAdminController
{
	// language array
	private $lang;
	// the manager object
	private $manager;

	/** ************************************************************************
	 * Default constructor.
	 * @param $_lang localized language array. see files in "lang" folder for possible values
	 */
	public function __construct(array &$_lang){
		$this->lang =& $_lang;
		$this->manager = EasyPollManager::construct();
	}

	/** ************************************************************************
	 * This method evaluates in what state we're in and runs the required methods
	 */
	public function run(){
		if($_GET['action'] == 'clearips'){
			$success = $this->manager->clearIPs();
			if($success){
				echo EasyPollController::message($this->lang['EP_clear_success'], '' , 'info');
			}
		}

		$clUrl = EasyPollController::getURL(array('action' => 'clearips'));
		echo	'<div class="sectionHeader">' . $this->lang['EP_admin_title'] . '</div>' .
				'<div class="sectionBody"><p>' . $this->lang['EP_admin_text'] . '</p>' .
				'<div class="actionButtons"><a href="' . $clUrl . '" class="button clear">' .
				$this->lang['EP_clear_ip'] . '</a><br clear="all"/></div>';

		echo	'</div>';
	}
}
?>