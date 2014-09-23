<?php
/**
 * ------------------------------------------------------------------------------
 * English Language File for EasyPoll Module. Keep this File UTF-8 encoded
 * ------------------------------------------------------------------------------
 * @author banal
 * @version 0.2 <2008-02-08>
 */

// ---- Formatting
$_lang['EP_date'] = '%Y-%m-%d %H:%i';

// ---- About screen
$_lang['EP_module_title'] = 'EasyPoll';
$_lang['EP_welcome_title'] = 'Добро пожаловать в EasyPoll Manager';
$_lang['EP_welcome_text'] = '<h1>EasyPoll Manager</h1><p>Добро пожаловать в EasyPoll Manager, управление голосованием (опросом).</p>';
$_lang['EP_info_version'] = 'Версия: <strong>%s</strong><br/>Автор: <strong>Roman Schmid, AKA banal</strong>';
$_lang['EP_not_installed'] = 'EasyPoll Manager еще не установлен.';
$_lang['EP_installbutton'] = 'Нажмите для установки';

// ---- install screen
$_lang['EP_install_title'] = 'Инсталляция EasyPoll Manager';
$_lang['EP_installsuccess'] = '<h1>Инсталляция выполнена</h1><p>EasyPoll Manager успешно установлен. <br><strong>Внимание!</strong> Необходимо удалить файл <strong>setup.sql</strong> с Вашего сервера. Путь к файлу: %s</p>';

// ---- Button Titles
$_lang['EP_back'] = 'Назад';
$_lang['EP_edit'] = 'Редактировать';
$_lang['EP_delete'] = 'Удалить';
$_lang['EP_create'] = 'Создать';
$_lang['EP_save'] = 'Сохранить';
$_lang['EP_cancel'] = 'Отмена';
$_lang['EP_confirmdelete'] = 'Подвердить удаление';
$_lang['EP_editchoice'] = 'Редактировать ответы';
$_lang['EP_stop_editchoice'] = 'Закончить редактирование';

// ---- General stuff
$_lang['EP_title_create'] = 'Создать новый язык';

// ---- Tab Titles
$_lang['EP_tab_about'] = 'О модуле';
$_lang['EP_tab_language'] = 'Языки';
$_lang['EP_tab_polls'] = 'Опросы';
$_lang['EP_tab_admin'] = 'Административные задачи';

// ---- Language Screen
$_lang['EP_lang_title'] = 'Управление языками EasyPoll';
$_lang['EP_lang_text']
    = 'На этой вкладке Вы можете создавать, редактировать или удалять языки, для которых Вы хотите создавать Опросы. <br/><strong>Внимание!</strong> Названия всех опросов и ответов должны быть заполнены для всех языков, иначе опрос будет не активен.';
$_lang['EP_lang_short'] = 'Код языка (1-3 символа)';
$_lang['EP_lang_long'] = 'Название языка';
$_lang['EP_lang_del'] = 'Вы действительно хотите удалить язык "%s"? <strong>Внимание, все данные связанные с этим языком будут потеряны!</strong>';

// ---- Polls and Choices Screen
$_lang['EP_polls_title'] = 'Управление опросами EasyPoll';
$_lang['EP_polls_text'] = 'На этой вкладке Вы можете создавать, редактировать или удалять Опросы, а так же отслеживать их состояние<br/><br/><strong>T</strong> = Статус заполнения полей (подведите мышку к иконке, что бы увидеть детали). Опросы, поля которых не полностью заполнены, считаются <strong>неактивными</strong> и выводится не будут!<br/>
<strong>A</strong> = Активность Опроса. Неактивные Опросы никогда не будут выводится на сайте.<br/>
<strong>V</strong> = Текущее количество проголосовавших в Опросе.<br/>
<strong>C</strong> = Число Ответов для этого Опроса. Опросы, у которых нет ни одного варианта Ответа, считаются <strong>неактивными</strong>.';

$_lang['EP_poll_title'] = 'Внутреннее Название';
$_lang['EP_poll_sdate'] = 'Дата начала';
$_lang['EP_poll_edate'] = 'Дата окончания';
$_lang['EP_poll_active'] = 'Активен/неактивен';
$_lang['EP_poll_transl'] = 'Название';
$_lang['EP_poll_transl_short'] = '<span title="статус заполнения полей">T</span>';
$_lang['EP_poll_active_short'] = '<span title="активность опроса">A</span>';
$_lang['EP_poll_votes_short'] = '<span title="количество голосов">V</span>';
$_lang['EP_poll_choices_short'] = '<span title="число вариантов Ответа">C</span>';
$_lang['EP_poll_votes'] = 'Голоса';
$_lang['EP_poll_new'] = 'Создать новый Опрос';
$_lang['EP_poll_edit'] = 'Редактировать Опрос';
$_lang['EP_poll_del'] = 'Вы действительно хотите удалить Опрос "%s"?<br/><strong>Все данные, связанные с этим Опросом будут безвозвратно удалены!</strong>';

$_lang['EP_transl_complete'] = 'Поля заполнены';
$_lang['EP_transl_missing'] = '%d поля(ей) необходимо заполнить';

$_lang['EP_choices_title'] = 'Ответы на Опрос';
$_lang['EP_choice_new'] = 'Создать новый Ответ';
$_lang['EP_choice_edit'] = 'Редактировать Ответ';
$_lang['EP_choice_del'] = 'Вы действительно хотите удалить Ответ "%s"?<br/><strong>Все данные связанные с этим Ответом будут безвозвратно удалены!</strong>';

// ---- Admin Screen
$_lang['EP_admin_title'] = 'Административные задачи EasyPoll';
$_lang['EP_admin_text'] = '<strong>Внимание! Действия будут применены без подтверждения.</strong>';
$_lang['EP_clear_ip'] = 'Очистить IP-адреса проголосовавших';
$_lang['EP_clear_success'] = 'IP-адреса удалены';

// ---- ERRORS AND WARNINGS
$_lang['EP_error_title'] = 'Ошибка!';
$_lang['EP_warning_title'] = 'Внимание';
// %s placeholder will be replaced with file path
$_lang['EP_sqlfile_warn'] = 'Файл <strong>setup.sql</strong> все еще не удален. В целях безоспастности, удалите его с вашего сервера. <br/><strong>Path:</strong> %s';

$_lang['EP_sqlfile_error'] = '<h1>Ошибка инсталляции</h1>
<p>Файл <strong>setup.sql</strong> не существует или недоступен для чтения. Этот файл нужен для создания необходимых таблиц в базе данных. Пожалуйста, убедитесь что файл setup.sql находится в папке modules/EasyPoll/ и что он доступен для чтения. Если это не возможно (в случае запуска php в безопастном режиме) вам необходимо создать таблицы вручную, запустив SQL команды из данного файла в phpMyAdmin или другой программе администрирования вашей базы даных.</p>';

$_lang['EP_sqlcreate_error'] = '<h1>Ошибка базы данных</h1><p>Для инсталляции EasyPoll необходима <strong>MySQL Version 4.1</strong> или выше. Если ваша БД отвечает этому требованию, и Вы все еще получаете эту ошибку, вам необходимо создать таблицы вручную, запустив SQL команды из данного файла в phpMyAdmin или другой программе администрирования вашей базы даных.</p>';

// %s placeholder will be replaced with file path
$_lang['EP_noclassfile'] = 'Fatal Error: Class-File does not exist: %s';

$_lang['EP_ex_undef'] = 'Unspecified EasyPoll Exception: %s';
$_lang['EP_db_error'] = 'Ошибка базы данных. SQL команда завершилась с ошибкой';
$_lang['EP_ex_invalidparam'] = 'Не правильно заполнено поле: "%s"';
