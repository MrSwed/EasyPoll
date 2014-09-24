<?php
/**
 * ------------------------------------------------------------------------------
 * Easy Poll Language Controller
 * ------------------------------------------------------------------------------
 * Easy Poll Language Controller Class
 * This class encapsulates the presentation of the Easy Poll Language Administration.
 * It is responsible for HTML output and capturing user input. Logic should be
 * delegated to the EasyPollManager
 *
 * Dependencies/Requirements:
 * - EasyPollManager Class
 * - EasyPollController Class
 * - MODx >=1.0.10, others to be tested
 * - PHP Version 5 or greater
 * - MySQL Version 4.1 or better
 *
 * @author banal, vanchelo <brezhnev.ivan@yahoo.com>
 * @version 0.3.4 <2014-09-23>
 */

require_once(dirname(__FILE__) . '/EasyPollController.class.php');
require_once(dirname(__FILE__) . '/EasyPollManager.class.php');

class EasyPollLangController
{
    /**
     * @var DocumentParser
     */
    protected $modx;
    /**
     * @var array Language array
     */
    private $lang;
    /**
     * @var EasyPollManager Manager Controller
     */
    private $manager;

    /**
     * Default constructor
     *
     * @param DocumentParser $modx
     * @param array $_lang localized language array. see files in "lang" folder for possible values
     */
    public function __construct(DocumentParser & $modx, array & $_lang)
    {
        $this->modx =& $modx;
        $this->lang =& $_lang;
        $this->manager = EasyPollManager::instance($modx);
    }

    /**
     * This method evaluates in what state we're in and runs the required methods
     */
    public function run()
    {
        if (isset($_POST['save']) || isset($_POST['new'])) {
            try {
                $id = $_POST['save'] ? $_GET['langid'] : false;
                $this->manager->insertLanguage($id, $_POST['short'], $_POST['long']);
                unset($_GET['langid']);
            } catch (EasyPollException $ex) {
                echo EasyPollController::message(
                    $this->lang['EP_error_title'],
                    sprintf($this->lang[$ex->getMsgString()], $this->lang[$ex->getParamString()]),
                    'error'
                );
            }
        } else if (isset($_POST['cancel'])) {
            unset($_GET['langid']);
        } else if ($_GET['action'] == 'delete') {
            $arr = $this->manager->getLangById($_GET['langid']);
            $delUrl = EasyPollController::getURL(array('action' => 'reallydelete'));
            unset($_GET['langid']);
            unset($_GET['action']);
            $cancelUrl = EasyPollController::getURL();
            echo EasyPollController::message(
                $this->lang['EP_warning_title'],
                sprintf($this->lang['EP_lang_del'], $arr['long']) .
                '<div class="actionButtons"><a href="' . $delUrl . '" class="button confirm">' .
                $this->lang['EP_confirmdelete'] . '</a><a href="' . $cancelUrl . '" class="button cancel">' .
                $this->lang['EP_cancel'] . '</a></div>',
                'warning',
                true
            );
        } else if ($_GET['action'] == 'reallydelete') {
            $this->manager->deleteLanguage($_GET['langid']);
        }

        echo '<div class="sectionHeader">' . $this->lang['EP_lang_title'] . '</div>' .
            '<div class="sectionBody"><p>' . $this->lang['EP_lang_text'] . '</p>';
        echo $this->listLanguages(intval($_GET['langid']));

        echo '</div>';
    }

    /** ************************************************************************
     * List available languages as table
     */
    private function listLanguages($active = false)
    {
        $locknew = '';
        $lockcls = '';
        $formUrl = EasyPollController::getURL();
        $list = $this->manager->getLanguages();
        $buffer = '<form action="' . $formUrl . '" method="post">' .
            '<table class="grid langtable"><colgroup><col width="20%"/><col width="40%"/>' .
            '<col width="20%"/><col width="20%"/></colgroup><tbody>' .
            '<tr><th class="gridHeader">' . $this->lang['EP_lang_short'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_lang_long'] . '</th><th colspan="2" class="gridHeader"></th></tr>';
        if ($list) {
            foreach ($list as $row) {
                if ($active == $row['id']) {
                    $locknew = ' disabled="disabled"';
                    $lockcls = ' disabled';
                    $buffer .= '<tr class="active"><td><input type="text" name="short" size="3" maxlength="3" value="' . $row['short'] . '"/></td>' .
                        '<td><input type="text" name="long" value="' . $row['long'] . '"/></td>' .
                        '<td class="actionButtons"><input type="submit" name="save" class="button save" value="' .
                        $this->lang['EP_save'] . '"/></td>' .
                        '<td class="actionButtons"><input type="submit" name="cancel" class="button cancel" value="' .
                        $this->lang['EP_cancel'] . '"/></td></tr>';
                } else {
                    $editUrl = EasyPollController::getURL(array('action' => 'edit', 'langid' => $row['id']));
                    $delUrl = EasyPollController::getURL(array('action' => 'delete', 'langid' => $row['id']));
                    $buffer .= '<tr><td>' . $row['short'] . '</td><td>' . $row['long'] . '</td>' .
                        '<td class="actionButtons"><a href="' . $editUrl . '" class="button edit">' .
                        $this->lang['EP_edit'] . '</a></td>' .
                        '<td class="actionButtons"><a href="' . $delUrl . '" class="button delete">' .
                        $this->lang['EP_delete'] . '</a></td></tr>';
                }
            }
        }

        $buffer .= '<tr><th colspan="4" class="gridHeader">' . $this->lang['EP_title_create'] . '</th></tr>' .
            '<tr><td><input type="text" name="short" size="3" maxlength="3" value=""' . $locknew . ' class="' . $lockcls . '"/></td>' .
            '<td><input type="text" name="long" value=""' . $locknew . ' class="' . $lockcls . '"/></td>' .
            '<td class="actionButtons" colspan="2"><input type="submit" name="new" class="button save' . $lockcls . '" value="' .
            $this->lang['EP_create'] . '"' . $locknew . '/></td></tr>' .
            '</tbody></table></form>';
        return $buffer;
    }
}

?>
