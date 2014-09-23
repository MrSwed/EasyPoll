<?php
/**
 * ------------------------------------------------------------------------------
 * English Language File for EasyPoll Module. Keep this File UTF-8 encoded
 * ------------------------------------------------------------------------------
 * @author banal, vanchelo <brezhnev.ivan@yahoo.com>
 * @version 0.3.4 <2014-09-23>
 */

// ---- Formatting
$_lang['EP_date'] = '%Y-%m-%d %H:%i';

// ---- About screen
$_lang['EP_module_title'] = 'EasyPoll';
$_lang['EP_welcome_title'] = 'Welcome to the EasyPoll Manager';
$_lang['EP_welcome_text'] = '<h1>EasyPoll Manager</h1>
<p>Welcome to the EasyPoll Manager, a Module for MODx to create and administer Ajax enabled Polls.</p>';
$_lang['EP_info_version'] = 'Version: <strong>%s</strong><br/>Author: <strong>Roman Schmid, AKA banal</strong>';
$_lang['EP_not_installed'] = 'EasyPoll Manager is not installed yet.';
$_lang['EP_installbutton'] = 'Click here to install';

// ---- install screen
$_lang['EP_install_title'] = 'Installing EasyPoll Manager';
$_lang['EP_installsuccess'] = '<h1>Installation successful</h1><p>EasyPoll Manager was successfully installed. You may now delete the <strong>setup.sql</strong> file from your server. File path: %s</p>';

// ---- Button Titles
$_lang['EP_back'] = 'Back';
$_lang['EP_edit'] = 'Edit';
$_lang['EP_delete'] = 'Delete';
$_lang['EP_create'] = 'Create';
$_lang['EP_save'] = 'Save';
$_lang['EP_cancel'] = 'Cancel';
$_lang['EP_confirmdelete'] = 'Confirm deletion';
$_lang['EP_editchoice'] = 'Edit choices';
$_lang['EP_stop_editchoice'] = 'Stop editing choices';

// ---- General stuff
$_lang['EP_title_create'] = 'Create a new entry';


// ---- Tab Titles
$_lang['EP_tab_about'] = 'About';
$_lang['EP_tab_language'] = 'Languages';
$_lang['EP_tab_polls'] = 'Polls';
$_lang['EP_tab_admin'] = 'Administrative tasks';

// ---- Language Screen
$_lang['EP_lang_title'] = 'EasyPoll Language Management';
$_lang['EP_lang_text'] = 'Add, remove or change Languages you want to create Polls for.<br/>Please note that you must supply all Poll-questions and answers in every language you create here.';
$_lang['EP_lang_short'] = 'Language Code (1-3 letters)';
$_lang['EP_lang_long'] = 'Language Name';
$_lang['EP_lang_del'] = 'Do you really want to delete the Language \'%s\'? <strong>Please note, that this action will delete all Poll and
Choice Translations that where done for this specific Language!</strong>';

// ---- Polls and Choices Screen
$_lang['EP_polls_title'] = 'EasyPoll Polls Management';
$_lang['EP_polls_text'] = 'Add, remove or change Polls<br/><strong>T</strong> = Translation Status (roll over icon to see details).
Polls that are not fully translated are considered <strong>inactive</strong><br/>
<strong>A</strong> = is Poll active/visible? Inactive Polls are <strong>never</strong> visible on the website, no matter
what you enter as Start- or End-Date.<br/>
<strong>V</strong> = Number of votes for this poll<br/>
<strong>C</strong> = Number of Choices for this Poll. Polls that have zero choices are considered <strong>inactive</strong>';

$_lang['EP_poll_title'] = 'Internal Title';
$_lang['EP_poll_sdate'] = 'Start-date';
$_lang['EP_poll_edate'] = 'End-date';
$_lang['EP_poll_active'] = 'Visible';
$_lang['EP_poll_transl'] = 'Translation';
$_lang['EP_poll_transl_short'] = '<span title="Translation status">T</span>';
$_lang['EP_poll_active_short'] = '<span title="is Poll visible?">A</span>';
$_lang['EP_poll_votes_short'] = '<span title="Number of votes">V</span>';
$_lang['EP_poll_choices_short'] = '<span title="Number of choices">C</span>';
$_lang['EP_poll_votes'] = 'Votes';
$_lang['EP_poll_new'] = 'Create a new Poll';
$_lang['EP_poll_edit'] = 'Edit Poll';
$_lang['EP_poll_del'] = 'Do you really want to delete the Poll \'%s\'? <strong>Please note, that this action will delete all Choices, Votes and Translations
associated with this Poll</strong>';

$_lang['EP_transl_complete'] = 'Translation complete';
$_lang['EP_transl_missing'] = '%d items to be translated';

$_lang['EP_choices_title'] = 'Poll choices';
$_lang['EP_choice_new'] = 'Create a new choice';
$_lang['EP_choice_edit'] = 'Edit chocie';
$_lang['EP_choice_del'] = 'Do you really want to delete the Choice \'%s\'? <strong>Please note, that this action will delete all Translations and Votes associated with this Choice</strong>';

// ---- Admin Screen
$_lang['EP_admin_title'] = 'EasyPoll administrative tasks';
$_lang['EP_admin_text'] = 'Perform administrative tasks regarding your Polls.<br/><strong>Please note that these actions won\'t ask for confirmation. They will be executed immediately. So think before you click</strong>';
$_lang['EP_clear_ip'] = 'Clear all logged user IPs';
$_lang['EP_clear_success'] = 'The logged IP addresses have been deleted';

// ---- ERRORS AND WARNINGS

$_lang['EP_error_title'] = 'An error occured';
$_lang['EP_warning_title'] = 'Warning';
// %s placeholder will be replaced with file path
$_lang['EP_sqlfile_warn'] = 'The <strong>setup.sql</strong> file still exists. For safety reasons you should delete it from the server. <br/><strong>Path:</strong> %s';

$_lang['EP_sqlfile_error'] = '<h1>Setup failed</h1>
<p>The <strong>setup.sql</strong> file does not exist or is not readable. This file is required
in order to install the DB Tables. Please make sure you place the file in the modules/EasyPoll/
folder and make sure it is readable by PHP. If that\'s not possible (in case of php safe mode) you
should create the tables manually by running the SQL commands from the file in phpMyAdmin or another
Database Administration Tool.</p>';

$_lang['EP_sqlcreate_error'] = '<h1>Setup failed</h1>
<p>Creation of the required DB Tables failed. Please note, EasyPoll requires <strong>MySQL Version 4.1</strong> or higher. If your DB meets this requirement and you still get this error, please consider adding the Tables manually by running the create commands from the setup.sql file on your DB. Use phpMyAdmin or a similar tool for that task.</p>';

// %s placeholder will be replaced with file path
$_lang['EP_noclassfile'] = 'Fatal Error: Class-File does not exist: %s';

$_lang['EP_ex_undef'] = 'Unspecified EasyPoll Exception: %s';
$_lang['EP_db_error'] = 'Database error. A SQL Command failed';
$_lang['EP_ex_invalidparam'] = 'Invalid Parameter supplied for: \'%s\'';
