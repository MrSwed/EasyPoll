<?php
/**
 * ------------------------------------------------------------------------------
 * German Language File for EasyPoll Module. Keep this File UTF-8 encoded
 * ------------------------------------------------------------------------------
 * @author banal
 * @version 0.2 <2008-02-08>
 */

// ---- Formatting
$_lang['EP_date'] = '%d.%m.%Y %H:%i';

// ---- About screen
$_lang['EP_module_title'] = 'EasyPoll';
$_lang['EP_welcome_title'] = 'Willkomen beim EasyPoll Manager';
$_lang['EP_welcome_text']
= '<h1>EasyPoll Manager</h1>
<p>Willkommen beim EasyPoll Manager. Ein MODx Modul zum Verwalten von Online Abstimmungen</p>';
$_lang['EP_info_version'] = 'Version: <strong>%s</strong><br/>Autor: <strong>Roman Schmid, AKA banal</strong>';
$_lang['EP_not_installed'] = 'Das EasyPoll Modul ist noch nicht vollständig installiert';
$_lang['EP_installbutton'] = 'Hier klicken um Installation zu starten';

// ---- install screen
$_lang['EP_install_title'] = 'Installiere EasyPoll Manager';
$_lang['EP_installsuccess']
= '<h1>Installation erfolgreich durchgeführt</h1>
<p>Der EasyPoll Manager wurde erfolgreich installiert und kann nun verwendet werden.
Aus Sicherheitsgründen solltest du die Datei <strong>setup.sql</strong> vom Webserver löschen.<br/>
Pfad: %s</p>';

// ---- Button Titles
$_lang['EP_back'] = 'Zurück';
$_lang['EP_edit'] = 'Bearbeiten';
$_lang['EP_delete'] = 'Löschen';
$_lang['EP_create'] = 'Erstellen';
$_lang['EP_save'] = 'Speichern';
$_lang['EP_cancel'] = 'Abbrechen';
$_lang['EP_confirmdelete'] = 'Löschen bestätigen';
$_lang['EP_editchoice'] = 'Optionen bearbeiten';
$_lang['EP_stop_editchoice'] = 'Bearbeiten der Optionen beenden';

// ---- General stuff
$_lang['EP_title_create'] = 'Neuen Eintrag erstellen';


// ---- Tab Titles
$_lang['EP_tab_about'] = 'Über';
$_lang['EP_tab_language'] = 'Sprachen';
$_lang['EP_tab_polls'] = 'Abstimmungen';
$_lang['EP_tab_admin'] = 'Administrative Aufgaben';

// ---- Language Screen
$_lang['EP_lang_title'] = 'EasyPoll Sprachen-Verwaltung';
$_lang['EP_lang_text']
= 'Erstelle, lösche oder ändere Sprachen für deine Abstimmungen.<br/>Bitte beachte, dass die Abstimmungen für jede
hier definierte Sprache von dir übersetzt werden müssen.';

$_lang['EP_lang_short'] = 'Sprach-Kürzel (1-3 Buchstaben)';
$_lang['EP_lang_long'] = 'Sprache';
$_lang['EP_lang_del']
= 'Willst du wirklich die Sprache \'%s\' löschen? <strong>Beachte, dass diese Aktion auch sämtliche Übersetzungen die
in dieser Sprache erfasst worden sind löscht.</strong>';

// ---- Polls and Choices Screen
$_lang['EP_polls_title'] = 'EasyPoll Abstimmungen';
$_lang['EP_polls_text']
= 'Erstelle, lösche oder ändere deine Abstimmungen<br/>
<strong>Ü</strong> = Übersetzungs-Status (fahre mit der Maus über das Icon um mehr Informationen zu erhalten).
Alle Abstimmungen die nicht vollständig übersetzt worden sind, gelten als <strong>inaktiv</strong><br/>
<strong>A</strong> = ist die Abstimmung aktiv? Inaktive Abstimmungen sind <strong>nie</strong> auf der Website
sichtbar. Ganz egal was du als Start- oder End-Datum angegeben hast.<br/>
<strong>S</strong> = Anzahl der Stimmen die für diese Abstimmung eingegangen sind<br/>
<strong>O</strong> = Anzahl der Optionen für diese Abstimmung. Abstimmungen die keine Optionen enthalten gelten
als <strong>inaktiv</strong>';

$_lang['EP_poll_title'] = 'Interner Titel';
$_lang['EP_poll_sdate'] = 'Start-Datum';
$_lang['EP_poll_edate'] = 'End-Datum';
$_lang['EP_poll_active'] = 'Aktiv';
$_lang['EP_poll_transl'] = 'Übersetzung';
$_lang['EP_poll_transl_short'] = '<span title="Übersetzungs-Status">Ü</span>';
$_lang['EP_poll_active_short'] = '<span title="ist die Abstimmung aktiv?">A</span>';
$_lang['EP_poll_votes_short'] = '<span title="Anzahl eingegangener Stimmen">S</span>';
$_lang['EP_poll_choices_short'] = '<span title="Anzahl Optionen">O</span>';
$_lang['EP_poll_votes'] = 'Stimmen';
$_lang['EP_poll_new'] = 'Erstelle eine neue Abstimmung';
$_lang['EP_poll_edit'] = 'Bearbeite Abstimmung';
$_lang['EP_poll_del']
= 'Möchtest du wirklich die Abstimmung mit Namen \'%s\' löschen? <strong>Bitte beachte, dass dabei auch alle
Optionen, Stimmen und Übersetzungen die zu dieser Abstimmung gehören mit gelöscht werden</strong>';

$_lang['EP_transl_complete'] = 'Übersetzung komplett';
$_lang['EP_transl_missing'] = '%d Übersetzungen fehlen';

$_lang['EP_choices_title'] = 'Abstimmungs-Optionen';
$_lang['EP_choice_new'] = 'Erstelle eine neue Option';
$_lang['EP_choice_edit'] = 'Bearbeite Option';
$_lang['EP_choice_del']
= 'Möchtest du die Option mit Namen \'%s\' löschen? <strong>Bitte beachte, dass dabei auch die zugehörigen Stimmen und
Übersetzungen gelöscht werden</strong>';

// ---- Admin Screen
$_lang['EP_admin_title'] = 'EasyPoll administrative Aufgaben';
$_lang['EP_admin_text']
= 'Hier kannst du administrative Aufgaben erledigen.<br/><strong>Beachte, dass diese Aufgaben ungefragt ausgeführt
werden. Also erst überlegen, dann klicken.</strong>';
$_lang['EP_clear_ip'] = 'Lösche alle gespeicherten IP Adressen';
$_lang['EP_clear_success'] = 'Die gespeicherten IP Adressen wurden gelöscht.';

// ---- ERRORS AND WARNINGS

$_lang['EP_error_title'] = 'Ein Fehler ist aufgetreten';
$_lang['EP_warning_title'] = 'Warnung';
// %s placeholder will be replaced with file path
$_lang['EP_sqlfile_warn']
= 'Die Datei <strong>setup.sql</strong> ist noch auf dem Server vorhanden. Aus Sicherheitsgründen solltest du diese
löschen.<br/><strong>Pfad:</strong> %s';

$_lang['EP_sqlfile_error']
= '<h1>Installation fehlgeschlagen</h1>
<p>Die Datei <strong>setup.sql</strong> existiert nicht oder ist nicht lesbar. Diese Datei ist jedoch erforderlich
um dieses Modul zu installieren. Bitte versichere dich, dass die Datei im Ordner modules/EasyPoll/ liegt und durch
PHP lesbar ist. Falls dies nicht möglich sein sollte (z.B. im Falle von aktiviertem PHP safe-mode), erstelle die
benötigten Datenbank Tabellen von Hand indem du die Befehle in setup.sql auf deiner Datenbank absetzt (Beispielsweise
mittels phpMyAdmin).</p>';

$_lang['EP_sqlcreate_error']
= '<h1>Installation fehlgeschlagen</h1>
<p>Die benötigten Datenbank-Tabellen konnten nicht erstellt werden. Für ein korrektes Funktionieren wird
<strong>MySQL Version 4.1</strong> oder höher vorausgesetzt. Wenn deine Datenbank diese Anforderung erfüllt,
solltest du versuchen die Befehle in setup.sql manuell asuzuführen. Benutze dazu phpMyAdmin oder ein
ähnliches Datenbank-Verwaltungs Tool.</p>';

// %s placeholder will be replaced with file path
$_lang['EP_noclassfile'] = 'Fehler: Klassen-Datei existiert nicht: %s';

$_lang['EP_ex_undef'] = 'Unbekannte EasyPoll Ausnahme: %s';
$_lang['EP_db_error'] = 'Datenbank fehler. Ein SQL Befehl ist fehlgeschlagen';
$_lang['EP_ex_invalidparam'] = 'Ungültiger Parameter für: \'%s\'';
?>