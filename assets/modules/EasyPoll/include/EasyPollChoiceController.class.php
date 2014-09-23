<?php
/**
 * ------------------------------------------------------------------------------
 * Easy Poll Choice Controller
 * ------------------------------------------------------------------------------
 * Easy Poll Choice Controller Class
 * This class encapsulates the presentation of the Easy Poll Choice and Poll
 * Administration.
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

class EasyPollChoiceController
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
        if (isset($_POST['save'])) {
            try {
                $id = intval($_GET['pollid']);
                $langs = array();
                $match = array();
                foreach ($_POST as $k => $v) {
                    if (preg_match('/^lang_(\d+)$/', $k, $match)) {
                        if ($v != '')
                            $langs[$match[1]] = $v;
                    }
                }
                $sdate = trim($_POST['sdate']) == '' ? false : $_POST['sdate'];
                $edate = trim($_POST['edate']) == '' ? false : $_POST['edate'];
                $this->manager->insertPoll($id, $_POST['title'], $langs, $_POST['active'], $sdate, $edate);
                unset($_GET['pollid']);
            } catch (EasyPollException $ex) {
                echo EasyPollController::message(
                    $this->lang['EP_error_title'],
                    sprintf($this->lang[$ex->getMsgString()], $this->lang[$ex->getParamString()]),
                    'error'
                );
            }
        } else if (isset($_POST['cancel'])) {
            unset($_GET['pollid']);
        } else if ($_GET['action'] == 'delete') {
            $arr = $this->manager->getPollById($_GET['pollid']);
            $delUrl = EasyPollController::getURL(array('action' => 'reallydelete'));
            unset($_GET['pollid']);
            unset($_GET['action']);
            $cancelUrl = EasyPollController::getURL();
            echo EasyPollController::message(
                $this->lang['EP_warning_title'],
                sprintf($this->lang['EP_poll_del'], $arr['title']) .
                '<div class="actionButtons"><a href="' . $delUrl . '" class="button confirm">' .
                $this->lang['EP_confirmdelete'] . '</a><a href="' . $cancelUrl . '" class="button cancel">' .
                $this->lang['EP_cancel'] . '</a></div>',
                'warning',
                true
            );
        } else if ($_GET['action'] == 'reallydelete') {
            $this->manager->deletePoll($_GET['pollid']);
        } else if (isset($_POST['csave'])) {
            try {
                $idpoll = intval($_GET['pollid']);
                $idchoice = intval($_GET['choiceid']);
                $langs = array();
                $match = array();
                foreach ($_POST as $k => $v) {
                    if (preg_match('/^lang_(\d+)$/', $k, $match)) {
                        if ($v != '')
                            $langs[$match[1]] = $v;
                    }
                }
                $this->manager->insertChoice($idchoice, $idpoll, $_POST['title'], $langs);
                unset($_GET['choiceid']);
            } catch (EasyPollException $ex) {
                echo EasyPollController::message(
                    $this->lang['EP_error_title'],
                    sprintf($this->lang[$ex->getMsgString()], $this->lang[$ex->getParamString()]),
                    'error'
                );
            }
        } else if (isset($_POST['ccancel'])) {
            unset($_GET['choiceid']);
        } else if ($_GET['action'] == 'cdelete') {
            $arr = $this->manager->getChoiceById($_GET['choiceid']);
            $delUrl = EasyPollController::getURL(array('action' => 'creallydelete'));

            $cancelUrl = EasyPollController::getURL(array('action' => 'choice'), true, array('choiceid'));
            echo EasyPollController::message(
                $this->lang['EP_warning_title'],
                sprintf($this->lang['EP_choice_del'], $arr['title']) .
                '<div class="actionButtons"><a href="' . $delUrl . '" class="button confirm">' .
                $this->lang['EP_confirmdelete'] . '</a><a href="' . $cancelUrl . '" class="button cancel">' .
                $this->lang['EP_cancel'] . '</a></div>',
                'warning',
                true
            );
        } else if ($_GET['action'] == 'creallydelete') {
            $this->manager->deleteChoice($_GET['choiceid']);
        } else if ($_GET['action'] == 'cup') {
            $this->manager->sortChoice($_GET['choiceid'], true);
            unset($_GET['choiceid']);
        } else if ($_GET['action'] == 'cdown') {
            $this->manager->sortChoice($_GET['choiceid'], false);
            unset($_GET['choiceid']);
        }

        echo '<div class="sectionHeader">' . $this->lang['EP_polls_title'] . '</div>' .
            '<div class="sectionBody"><p>' . $this->lang['EP_polls_text'] . '</p>';
        echo $this->listPolls($_GET['pollid']);
        echo '</div>';

        $stopUrl = EasyPollController::getURL(array(), true, array('action', 'choiceid', 'pollid'));
        if (
            $_GET['action'] == 'choice'
            || $_GET['action'] == 'cedit'
            || $_GET['action'] == 'csave'
            || $_GET['action'] == 'cnew'
            || $_GET['action'] == 'creallydelete'
            || $_GET['action'] == 'cup'
            || $_GET['action'] == 'cdown'
        ) {
            echo $this->listChoices($_GET['pollid'], $_GET['choiceid']);
            echo '<br/>';
            echo $this->choiceForm($_GET['pollid'], $_GET['choiceid']);
            echo '<div class="actionButtons"><a href="' . $stopUrl . '" class="button stop">' . $this->lang['EP_stop_editchoice'] . '</a></div>';
            echo '<br clear="all"/></div>';
        } else if ($_GET['action'] == 'cdelete') {
            echo $this->listChoices($_GET['pollid'], $_GET['choiceid']);
            echo '<div class="actionButtons"><a href="' . $stopUrl . '" class="button stop">' . $this->lang['EP_stop_editchoice'] . '</a></div>';
            echo '<br clear="all"/></div>';
        } else {
            echo $this->pollForm($_GET['pollid']);
        }
    }

    /**
     * List available polls as table
     *
     * @param bool|int $pollId
     *
     * @return string
     */
    private function listPolls($pollId = false)
    {
        $list = $this->manager->getPolls($this->lang['EP_date']);

        $buffer = '<table class="grid polltable"><tbody>' .
            '<tr><th class="gridHeader">#</th><th class="gridHeader">' . $this->lang['EP_poll_title'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_sdate'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_edate'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_active_short'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_transl_short'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_votes_short'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_choices_short'] . '</th>' .
            '<th colspan="3" class="gridHeader"></th></tr>';
        if ($list) {
            foreach ($list as $row) {
                $editUrl = EasyPollController::getURL(array('action' => 'edit', 'pollid' => $row['id']), true, array('choiceid'));
                $choiceUrl = EasyPollController::getURL(array('action' => 'choice', 'pollid' => $row['id']), true, array('choiceid'));
                $delUrl = EasyPollController::getURL(array('action' => 'delete', 'pollid' => $row['id']), true, array('choiceid'));
                $sdate = $row['sdate'] ? $row['sdate'] : '-';
                $edate = $row['edate'] ? $row['edate'] : '-';
                $trans = $row['translate'] == 0 ? '<a title="' . $this->lang['EP_transl_complete'] . '" class="translation_ok"><span>&nbsp;</span></a>' :
                    '<a title="' . sprintf($this->lang['EP_transl_missing'], $row['translate']) . '" class="translation_miss"><span>x</span></a>';
                $active = $row['active'] ? '<span class="item_active"><span>yes</span></span>' : '';
                if ($row['id'] == $pollId) {
                    $buffer .= '<tr class="active">';
                } else {
                    $buffer .= '<tr>';
                }
                $buffer .= '<td>' . $row['id'] . '</td><td>' . $row['title'] . '</td><td>' . $sdate . '</td>' .
                    '<td>' . $edate . '</td><td>' . $active . '</td>' .
                    '<td>' . $trans . '</td><td>' . intval($row['votes']) . '</td><td>' . $row['choices'] . '</td>';

                if ($row['id'] == $pollId) {
                    $buffer .= '<td colspan="3"></td></tr>';
                } else {
                    $buffer .= '<td class="actionButtons"><a href="' . $editUrl . '" class="button edit">' .
                        $this->lang['EP_edit'] . '</a></td>' .
                        '<td class="actionButtons"><a href="' . $choiceUrl . '" class="button choice">' .
                        $this->lang['EP_editchoice'] . '</a></td>' .
                        '<td class="actionButtons"><a href="' . $delUrl . '" class="button delete">' .
                        $this->lang['EP_delete'] . '</a></td></tr>';
                }
            }
        }

        $buffer .= '</tbody></table>';

        return $buffer;
    }

    /**
     * Create a form for a new or a existing form
     *
     * @param bool|int $id
     *
     * @return string
     *
     * @throws EasyPollException
     */
    private function pollForm($id = null)
    {
        $values = array();
        if ($id === null) {
            $formUrl = EasyPollController::getURL(array('action' => 'new'));
            $title = $this->lang['EP_poll_new'];
        } else {
            $formUrl = EasyPollController::getURL(array('action' => 'edit'));
            $title = $this->lang['EP_poll_edit'];
            $values = $this->manager->getPollById($id, '%d-%m-%Y %H:%i:%s');
        }
        $checked = $values['active'] ? 'checked="checked"' : '';
        $buffer = '<div class="sectionHeader">' . $title . '</div>' .
            '<div class="sectionBody"><form action="' . $formUrl . '" method="post" name="pollform"><fieldset>' .
            '<table class="formtable"><tbody>' .
            '<tr><th>' . $this->lang['EP_poll_title'] . '</th><td>' .
            '<input size="40" maxlength="128" type="text" name="title" value="' . $values['title'] . '" /></td></tr>' .
            '<tr><th>' . $this->lang['EP_poll_sdate'] . '</th><td>' .
            '<input size="20" maxlength="20" type="text" name="sdate" value="' . $values['sdate'] . '"/>' .
            '<a onclick="cal1.popup();" class="editdate"><span>edit</span></a></td></tr>' .
            '<tr><th>' . $this->lang['EP_poll_edate'] . '</th><td>' .
            '<input size="20" maxlength="20" type="text" name="edate" value="' . $values['edate'] . '"/>' .
            '<a onclick="cal2.popup();" class="editdate"><span>edit</span></a></td></tr>' .
            '<tr><th>' . $this->lang['EP_poll_active'] . '</th><td>' .
            '<input type="checkbox" name="active" value="1" ' . $checked . '/></td></tr>';

        $buffer .= $this->langFields($id);

        $buffer .= '<tr><th></th><td class="actionButtons"><input type="submit" name="save" class="button save" value="' .
            $this->lang['EP_save'] . '"/>';

        if ($id) {
            $buffer .= '<input type="submit" name="cancel" class="button cancel" value="' . $this->lang['EP_cancel'] . '"/></td></tr>';
        } else {
            $buffer .= '</td></tr>';
        }
        $buffer .= '</tbody></table></fieldset></form></div>';
        $buffer .= '
		<script type="text/javascript">
		// <!--
		    var cal1 = new calendar1(document.forms[\'pollform\'].elements[\'sdate\'], "");
		    cal1.path="' . $this->modx->getManagerPath() . 'media/";
		    cal1.year_scroll = true;
		    cal1.time_comp = true;


		    var cal2 = new calendar1(document.forms[\'pollform\'].elements[\'edate\'], "");
		    cal2.path="' . $this->modx->getManagerPath() . 'media/";
		    cal2.year_scroll = true;
		    cal2.time_comp = true;
		// -->
		</script>';

        return $buffer;
    }

    /**
     * List available choices as table
     *
     * @param int $idPoll
     * @param bool|int $choiceid
     *
     * @return string
     */
    private function listChoices($idPoll, $choiceid = false)
    {
        $list = $this->manager->getChoices($idPoll);

        $buffer = '<div class="sectionHeader">' . $this->lang['EP_choices_title'] . '</div>' .
            '<div class="sectionBody"><table class="grid polltable"><tbody>' .
            '<tr><th class="gridHeader">' . $this->lang['EP_poll_title'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_transl'] . '</th>' .
            '<th class="gridHeader">' . $this->lang['EP_poll_votes'] . '</th>' .
            '<th colspan="4" class="gridHeader"></th></tr>';
        if ($list) {
            $total = count($list);
            foreach ($list as $row) {
                $editUrl = EasyPollController::getURL(array('action' => 'cedit', 'choiceid' => $row['id']));
                $delUrl = EasyPollController::getURL(array('action' => 'cdelete', 'choiceid' => $row['id']));
                $upUrl = EasyPollController::getURL(array('action' => 'cup', 'choiceid' => $row['id']));
                $downUrl = EasyPollController::getURL(array('action' => 'cdown', 'choiceid' => $row['id']));

                $trans = $row['translate'] == 0 ? '<a title="' . $this->lang['EP_transl_complete'] . '" class="translation_ok"><span>&nbsp;</span></a>' :
                    '<a title="' . sprintf($this->lang['EP_transl_missing'], $row['translate']) . '" class="translation_miss"><span>x</span></a>';

                if ($row['id'] == $choiceid) {
                    $buffer .= '<tr class="active">';
                } else {
                    $buffer .= '<tr>';
                }

                $buffer .= '<td>' . $row['title'] . '</td><td>' . $trans . '</td><td>' . intval($row['votes']) . '</td>';

                if ($total > 1) {
                    if ($row['sorting'] == 1) {
                        $buffer .= '<td></td><td><a href="' . $downUrl . '" class="movedown"/><span>down</span></a></td>';
                    } else if ($row['sorting'] == $total) {
                        $buffer .= '<td><a href="' . $upUrl . '" class="moveup"/><span>up</span></a></td><td></td>';
                    } else {
                        $buffer .= '<td><a href="' . $upUrl . '" class="moveup"/><span>up</span></a></td>' .
                            '<td><a href="' . $downUrl . '" class="movedown"/><span>down</span></a></td>';
                    }
                } else {
                    $buffer .= '<td></td><td></td>';
                }

                if ($row['id'] == $choiceid) {
                    $buffer .= '<td colspan="2"></td></tr>';
                } else {
                    $buffer .= '<td class="actionButtons"><a href="' . $editUrl . '" class="button edit">' .
                        $this->lang['EP_edit'] . '</a></td>' .
                        '<td class="actionButtons"><a href="' . $delUrl . '" class="button delete">' .
                        $this->lang['EP_delete'] . '</a></td></tr>';
                }
            }
        }

        $buffer .= '</tbody></table>';

        return $buffer;
    }

    /**
     * Create a form for a new or a existing form
     *
     * @param $idPoll
     * @param bool|int $choiceid
     *
     * @return string
     */
    private function choiceForm($idPoll, $choiceid = false)
    {
        if ($choiceid == false) {
            $formUrl = EasyPollController::getURL(array('action' => 'cnew'));
            $title = $this->lang['EP_choice_new'];
            $choiceid = -1;
        } else {
            $formUrl = EasyPollController::getURL(array('action' => 'cedit'));
            $title = $this->lang['EP_choice_edit'];
            $values = $this->manager->getChoiceById($choiceid);
        }
        $buffer = '<div class="sectionHeader">' . $title . '</div>' .
            '<div class="sectionBody"><form action="' . $formUrl . '" method="post" name="choiceform"><fieldset>' .
            '<table class="formtable"><tbody>' .
            '<tr><th>' . $this->lang['EP_poll_title'] . '</th><td>' .
            '<input size="40" maxlength="128" type="text" name="title" value="' . $values['title'] . '" /></td></tr>';

        $buffer .= $this->langFields($idPoll, $choiceid);

        $buffer .= '<tr><th></th><td class="actionButtons"><input type="submit" name="csave" class="button save" value="' .
            $this->lang['EP_save'] . '"/>';

        if ($choiceid > 0) {
            $buffer .= '<input type="submit" name="ccancel" class="button cancel" value="' . $this->lang['EP_cancel'] . '"/></td></tr>';
        } else {
            $buffer .= '</td></tr>';
        }
        $buffer .= '</tbody></table></fieldset></form></div>';

        return $buffer;
    }

    /**
     * Create form fields for translations
     *
     * @param int $idPoll
     * @param int $idChoice
     *
     * @return string
     */
    private function langFields($idPoll = 0, $idChoice = 0)
    {
        $langs = $this->manager->getLanguages();
        $buffer = '';
        foreach ($langs as $arr) {
            if ($idPoll) {
                $value = $this->manager->getTranslation($idPoll, $arr['id'], $idChoice);
            } else {
                $value = '';
            }

            $buffer .= '<tr><th>' . $this->lang['EP_poll_transl'] . ' (' . $arr['short'] . ')</th><td>' .
                '<input type="text" name="lang_' . $arr['id'] . '" size="40" value="' . $value . '"/></td></tr>';
        }

        return $buffer;
    }
}
