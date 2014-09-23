<?php
/**
 * ------------------------------------------------------------------------------
 * Easy Poll Controller
 * ------------------------------------------------------------------------------
 * Easy Poll Controller Class
 * This class encapsulates the presentation of the EasyPoll Module.
 * It is responsible for HTML output and capturing user input. Logic should be
 * delegated to the EasyPollManager
 *
 *
 * Dependencies/Requirements:
 * - EasyPollManager Class
 * - MODx >=1.0.10, others to be tested
 * - PHP Version 5 or greater
 * - MySQL Version 4.1 or better
 *
 * @author banal, vanchelo <brezhnev.ivan@yahoo.com>
 * @version 0.3.4 <2014-09-23>
 */

require_once(dirname(__FILE__) . '/EasyPollLangController.class.php');
require_once(dirname(__FILE__) . '/EasyPollChoiceController.class.php');
require_once(dirname(__FILE__) . '/EasyPollAdminController.class.php');

class EasyPollController
{
    const VERSION = '0.3.4';

    /**
     * @var DocumentParser
     */
    protected $modx;
    /**
     * @var array Language array
     */
    private $lang;
    // array containing table names that should be created
    private $tables;
    // the path to the EasyPoll Directory
    private $path;
    // the different tabs
    private $tabs;
    // flag that indicates if setup is done
    private $isInstalled;

    private static $msgCount = 0;

    /**
     * Default constructor
     *
     * @param DocumentParser $modx
     * @param array $_lang localized language array. see files in "lang" folder
     * for possible values
     * @param string $path The path to the EasyPoll directory
     */
    public function __construct(DocumentParser & $modx, array & $_lang, $path)
    {
        $this->modx =& $modx;
        $this->lang =& $_lang;
        $this->tables = array(
            'ep_choice',
            'ep_language',
            'ep_poll',
            'ep_translation',
            'ep_userip'
        );

        $this->path = trim($path);
        $this->isInstalled = $this->getIsInstalled();

        // fill the tabs menu accordingly
        if ($this->isInstalled) {
            $this->tabs = array(
                'about' => $this->lang['EP_tab_about'],
                'languages' => $this->lang['EP_tab_language'],
                'polls' => $this->lang['EP_tab_polls'],
                'admin' => $this->lang['EP_tab_admin']
            );
        } else {
            $this->tabs = array(
                'about' => $this->lang['EP_tab_about']
            );
        }
    }

    /**
     * This method evaluates in what state we're in and runs the required methods
     *
     * @return void
     */
    public function run()
    {
        echo $this->htmlHeader();
        echo '<div class="dynamic-tab-pane-control">';
        echo $this->buildMenu($_GET['view']);
        echo '<div class="tab-page">';

        if ($_POST['view'] == 'setup') {
            echo $this->viewSetup();
        } else if ($_GET['view'] == 'languages') {
            // let the LanguageController do the work
            $controller = new EasyPollLangController($this->modx, $this->lang);
            $controller->run();
        } else if ($_GET['view'] == 'polls') {
            // let the Poll/Choice Controller do the work
            $controller = new EasyPollChoiceController($this->modx, $this->lang);
            $controller->run();
        } else if ($_GET['view'] == 'admin') {
            // let the Admin Controller do the work
            $controller = new EasyPollAdminController($this->modx, $this->lang);
            $controller->run();
        } else {
            // default action
            echo $this->viewSplash();
        }

        echo '</div></div>';
        echo $this->htmlFooter();
    }

    /**
     * Show splash page, view method
     *
     * @return string Html splash screen
     */
    private function viewSplash()
    {
        $url = self::getURL();

        $buffer = '<div class="sectionHeader">' . $this->lang['EP_welcome_title'] . '</div>' .
            '<div class="sectionBody"><div class="splash">' . $this->lang['EP_welcome_text'] .
            '<p>' . sprintf($this->lang['EP_info_version'], self::VERSION) . '</p></div>';

        $sqlfile = $this->path . 'setup.sql';

        if ($this->isInstalled) {
            // seems like we have all tables we need at this point
            if (file_exists($sqlfile)) // show a warning if sql file is still there
            {
                $buffer .= '<div class="warning">' . sprintf($this->lang['EP_sqlfile_warn'], $sqlfile) . '</div>';
            }
        } else {
            // not installed yet
            $buffer .= '<div class="installbox">' . $this->lang['EP_not_installed'] .
                '<form action="' . $url . '" method="post"><fieldset>' .
                '<input type="hidden" name="view" value="setup"/>' .
                '<input type="submit" value="' . $this->lang['EP_installbutton'] . '"/>' .
                '</fieldset></form></div>';
        }

        $buffer .= '</div>';

        return $buffer;
    }

    /**
     * Show setup page, view method
     *
     * @return string Html setup page
     */
    private function viewSetup()
    {

        $url = self::getURL();
        $buffer = '<div class="sectionHeader">' . $this->lang['EP_install_title'] . '</div>' .
            '<div class="sectionBody">';

        $buffer .= $this->setup();

        $buffer .= '<div class="actionButtons"><a href="' . $url . '" class="button back">' .
            $this->lang['EP_back'] . '</a></div><br clear="all"/></div>';

        return $buffer;
    }

    /**
     * Build Tab Menu
     *
     * @param string $active
     *
     * @return string
     */
    public function buildMenu($active = 'about')
    {
        if (!$active) $active = 'about';

        $buffer = '<div class="tab-row">';
        foreach ($this->tabs as $k => $v) {
            $url = self::getURL(array('a' => $_GET['a'], 'id' => $_GET['id'], 'view' => $k), false);
            if ($active == $k) {
                $buffer .= '<a href="' . $url . '" class="tab selected"><span>' . $v . '</span></a>';
            } else {
                $buffer .= '<a href="' . $url . '" class="tab"><span>' . $v . '</span></a>';
            }
        }

        $buffer .= '</div>';

        return $buffer;
    }

    /**
     * Setup all required Database Tables by reading setup.sql file
     *
     * @return string Html formatted message if successful or not
     */
    private function setup()
    {
        $sqlfile = $this->path . 'setup.sql';

        // show error message if .sql file is not readable
        if (!file_exists($sqlfile) || !is_readable($sqlfile)) {
            return '<div class="error">' . $this->lang['EP_sqlfile_error'] . '</div>';
        }

        // read the file
        $fp = fopen($sqlfile, 'r');
        $sql = fread($fp, filesize($sqlfile));
        fclose($fp);

        // replace table names with the modx prefix + table name
        foreach ($this->tables as $name) {
            $sql = str_replace('{' . $name . '}', $this->modx->getFullTableName($name), $sql);
        }

        // get the create table commands and run them
        $matches = array();
        preg_match_all('/CREATE TABLE.*?;/ims', $sql, $matches);

        // now $matches[0] should contain a array with all create table commands
        // run this in a transaction to ensure we're creating either all or no tables!
        $this->modx->db->query('SET AUTOCOMMIT=0;');
        $this->modx->db->query('START TRANSACTION;');
        $errors = false;

        foreach ($matches[0] as $sqlcmd) {
            $rs = $this->modx->db->query($sqlcmd);
            if (!$rs) {
                $errors = true;
                break;
            }
        }

        if ($errors) {
            $this->modx->db->query('ROLLBACK;');
        } else {
            $this->modx->db->query('COMMIT;');
        }
        $this->modx->db->query('SET AUTOCOMMIT=1;');

        // if we encountered errors we must stop here...
        if ($errors) {
            return '<div class="error">' . $this->lang['EP_sqlcreate_error'] . '</div>';
        } else {
            @unlink($sqlfile);
        }

        return '<div class="success">' . sprintf($this->lang['EP_installsuccess'], $sqlfile) . '</div>';
    }

    /**
     * Check if the required Tables are installed
     *
     * @return bool True if installation is ok
     */
    private function getIsInstalled()
    {
        $database = $this->modx->db->config['dbase'];
        $tbl_prefix = $this->modx->db->config['table_prefix'];
        // get all tables that use the modx_ prefix + ep_ prefix
        $rs = $this->modx->db->query('SHOW TABLES FROM ' . $database . ' LIKE \'' . $tbl_prefix . 'ep_%\'');

        if ($this->modx->db->getRecordCount($rs) >= count($this->tables)) {
            return true;
        }

        return false;
    }

    /**
     * HTML Header output
     *
     * @return string The html header
     */
    private function htmlHeader()
    {
        $tbl_prefix = $this->modx->db->config['table_prefix'];

        $rs = $this->modx->db->select(
            'setting_value', '`' . $tbl_prefix . 'system_settings`',
            'setting_name=\'manager_theme\'', ''
        );
        $row = $this->modx->db->getRow($rs);
        $theme = ($row['setting_value'] <> '') ? '/' . $row['setting_value'] : '';

        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ' .
        '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" ' .
        ($this->modx->config['manager_direction'] == 'rtl' ? 'dir="rtl"' : '') . ' lang="' .
        $this->modx->config['manager_lang_attribute'] . '" xml:lang="' . $this->modx->config['manager_lang_attribute'] . '">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>' . $this->lang['EP_module_title'] . '</title>
			<script type="text/javascript">var MODX_MEDIA_PATH = "media";</script>
			<link rel="stylesheet" type="text/css" href="media/style' . $theme . '/style.css" />
			<link rel="stylesheet" type="text/css" href="media/style' . $theme . '/coolButtons2.css" />
			<link rel="stylesheet" type="text/css" href="media/style' . $theme . '/tabs.css"/>
			<link rel="stylesheet" type="text/css" href="../assets/modules/EasyPoll/css/styles.css" />
			<script src="media/script/mootools/mootools.js" type="text/javascript"></script>
			<script type="text/javascript" src="media/script/datefunctions.js"></script>
		</head>
		<body><div class="container">';
    }

    /**
     * Html footer output
     *
     * @return string The html footer, basically just </body></html>
     */
    private function htmlFooter()
    {
        return '</div></body></html>';
    }

    /**
     * Helper method that adds a query string to the current url
     *
     * @param array $params Associative array containing key -> value pairs
     * @param bool $useGet Include variables from $_GET into the array
     * @param array $remove Variables to remove from query string
     *
     * @return string The built URL
     */
    public static function getURL(array $params = array(), $useGet = true, array $remove = array())
    {
        $self = $_SERVER['PHP_SELF'];
        if ($useGet) $params = array_merge($_GET, $params);

        foreach ($remove as $item) {
            if (isset($params[$item])) unset($params[$item]);
        }

        return $self . '?' . http_build_query($params, '', '&amp;');
    }

    /**
     * Helper method to create a removable message element
     *
     * @param string $title Message title
     * @param string $msg Message text
     * @param string $type The message type. valid values are = info, warning, error
     * @param bool $noClose
     *
     * @return string The built message html code
     */
    public static function message(&$title, $msg, $type = 'info', $noClose = false)
    {
        self::$msgCount++;

        if ($noClose) {
            return '<div id="EP_message_' . self::$msgCount . '" class="message ' . $type . '">' .
            '<div class="msg"><h2>' . $title . '</h2><p>' . $msg . '</p></div>' .
            '</div><br clear="all"/>';
        } else {
            return '<div id="EP_message_' . self::$msgCount . '" class="message ' . $type . '">' .
            '<div class="msg"><h2>' . $title . '</h2><p>' . $msg . '</p></div>' .
            '<a href="#" onclick="$(\'EP_message_' . self::$msgCount . '\').remove(); return false;"' .
            ' class="messageclose"><span>X</span></a></div><br clear="all"/>';
        }
    }
}
