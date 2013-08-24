<?php
/**
 * ------------------------------------------------------------------------------
 * English Language File for EasyPoll Module. Keep this File UTF-8 encoded
 * ------------------------------------------------------------------------------
 * @author Peter Ruiter - nrbcc.nl
 * @version 0.2 <2008-02-15>
 */

// ---- Formatting
$_lang['EP_date'] = '%d-%m-%Y %H:%i';

// ---- About screen
$_lang['EP_module_title'] = 'EasyPoll';
$_lang['EP_welcome_title'] = 'Welkom bij de EasyPoll Manager';
$_lang['EP_welcome_text']
= '<h1>EasyPoll Manager</h1>
<p>Welcome bij de EasyPoll Manager, een module voor MODx om op Ajax gebaseerde polls te maken en te beheren.</p>';
$_lang['EP_info_version'] = 'Versie: <strong>%s</strong><br/>Auteur: <strong>Roman Schmid, AKA banal</strong>';
$_lang['EP_not_installed'] = 'EasyPoll Manager is nog niet geinstalleerd.';
$_lang['EP_installbutton'] = 'Klik hier om te installeren.';

// ---- install screen
$_lang['EP_install_title'] = 'Installeren van EasyPoll Manager';
$_lang['EP_installsuccess']
= '<h1>Installatie successvol</h1>
<p>EasyPoll Manager is succesvol geinstalleerd. U kan nu het <strong>setup.sql</strong> bestand van uw server verwijderen. Bestandslocatie: %s</p>';

// ---- Button Titles
$_lang['EP_back'] = 'Terug';
$_lang['EP_edit'] = 'Wijzig';
$_lang['EP_delete'] = 'Verwijder';
$_lang['EP_create'] = 'Nieuw';
$_lang['EP_save'] = 'Opslaan';
$_lang['EP_cancel'] = 'Annuleer';
$_lang['EP_confirmdelete'] = 'Bevestig verwijderen';
$_lang['EP_editchoice'] = 'Wijzig keuzes';
$_lang['EP_stop_editchoice'] = 'Stop wijzigen keuzes';

// ---- General stuff
$_lang['EP_title_create'] = 'Nieuw item';


// ---- Tab Titles
$_lang['EP_tab_about'] = 'Over';
$_lang['EP_tab_language'] = 'Talen';
$_lang['EP_tab_polls'] = 'Polls';
$_lang['EP_tab_admin'] = 'Administratieve taken';

// ---- Language Screen
$_lang['EP_lang_title'] = 'EasyPoll Taal Beheer';
$_lang['EP_lang_text']
= 'Verwijder, wijzig of voeg talen toe waarvoor u Polls wilt aanmaken. <br/>Let op dat u alle Poll vragen en antwoorden moet ingeven voor elke taal die u hier aanmaakt.';
$_lang['EP_lang_short'] = 'Taal code (1-3 letters)';
$_lang['EP_lang_long'] = 'Taal naam';
$_lang['EP_lang_del']
= 'Weet u zeker dat u de taal \'%s\' wilt verwijderen? <strong>Let op dat deze actie de poll en keuze vertalingen voor deze specifieke taal zal verwijderen!</strong>';

// ---- Polls and Choices Screen
$_lang['EP_polls_title'] = 'EasyPoll Polls Beheer';
$_lang['EP_polls_text']
= 'Toevoegen, verwijderen of wijzigen van polls<br/><strong>T</strong> = Vertalingsstatus (mouse over het icoon om de details te zien).
Polls die niet geheel zijn vertaald worden gezien als <strong>niet-actief</strong><br/>
<strong>A</strong> = Is de Poll actief/zichtbaar? Niet-actieve polls zijn <strong>nooit</strong> zichtbaar op de website, ongeacht welke start- of einddatum u ingeeft..<br/>
<strong>V</strong> = Aantal stemmen voor deze poll<br/>
<strong>C</strong> = Aantal keuzes voor deze poll. Poll zonder keuzes worden gezien als <strong>niet-actief</strong>';

$_lang['EP_poll_title'] = 'Interne Titel';
$_lang['EP_poll_sdate'] = 'Start-datum';
$_lang['EP_poll_edate'] = 'Eind-datum';
$_lang['EP_poll_active'] = 'Zichtbaar';
$_lang['EP_poll_transl'] = 'Vertaling';
$_lang['EP_poll_transl_short'] = '<span title="Vertaling status">T</span>';
$_lang['EP_poll_active_short'] = '<span title="is Poll zichtbaar?">A</span>';
$_lang['EP_poll_votes_short'] = '<span title="Aantal stemmen">V</span>';
$_lang['EP_poll_choices_short'] = '<span title="Aantal keuzes">C</span>';
$_lang['EP_poll_votes'] = 'Stemmen';
$_lang['EP_poll_new'] = 'Nieuwe poll aanmaken';
$_lang['EP_poll_edit'] = 'Wijzig poll';
$_lang['EP_poll_del']
= 'Weet u zeker dat u de poll \'%s\' wilt verwijderen? <strong>Let op: deze actie zal alle keuzes, stemmen en vertalingen die bij deze poll horen ook verwijderen!</strong>';

$_lang['EP_transl_complete'] = 'Vertaling voltooid';
$_lang['EP_transl_missing'] = '%d items moeten nog worden vertaald';

$_lang['EP_choices_title'] = 'Poll keuzes';
$_lang['EP_choice_new'] = 'Nieuwe keuze aanmaken';
$_lang['EP_choice_edit'] = 'Wijzig keuze';
$_lang['EP_choice_del']
= 'Weet u zeker dat u de keuze \'%s\' wilt verwijderen? <strong>Let op: deze actie zal alle vertalingen en stemmen die bij deze keuze horen verwijderen!</strong>';

// ---- Admin Screen
$_lang['EP_admin_title'] = 'EasyPoll administratieve taken';
$_lang['EP_admin_text']
= 'Administratieve taken m.b.t uw polls uitvoeren.<br/><strong>Let op dat al deze acties geen bevestiging vereisen. Ze worden direct uitgevoerd, dus let op voordat u klikt.</strong>';
$_lang['EP_clear_ip'] = 'Leeg alle gelogde gebruiker IP adressen';
$_lang['EP_clear_success'] = 'De gelogde IP adressen zijn verwijderd.';

// ---- ERRORS AND WARNINGS

$_lang['EP_error_title'] = 'Er trad een fout op';
$_lang['EP_warning_title'] = 'Waarschuwing';
// %s placeholder will be replaced with file path
$_lang['EP_sqlfile_warn']
= 'Het <strong>setup.sql</strong> bestand bestaat nog steeds. Voor veiligheidsredenen dient dit bestand te worden verwijderd van de server. <br/><strong>Bestandslocatie:</strong> %s';

$_lang['EP_sqlfile_error']
= '<h1>Setup mislukt</h1>
<p>Het <strong>setup.sql</strong> bestand bestaat niet of is niet leesbaar. Het bestand is vereist om de Database tabellen te installeren. Verzeker u ervan dat het bestand in de modules/EasyPoll/ map staat en dat het bestand leesbaar is voor PHP. Als dat niet mogelijk is (bijvoorbeeld in het geval van PHP safe mode) dient u de tabellen zelf te maken met behulp van SQL via PHPmyAdmin of soortgelijke Database administratie tool.</p>';

$_lang['EP_sqlcreate_error']
= '<h1>Setup mislukt</h1>
<p>Het aanmaken van de vereiste DB tabellen is niet gelukt. Zorg ervoor dat u minstens <strong>MySQL Versie 4.1</strong> of hoger heeft. Als dit wel het geval is u krijgt nog steeds deze foutmelding dient u de database tabellen handmatig aan te maken via bijvoorbeeld PHPmyAdmin.</p>';

// %s placeholder will be replaced with file path
$_lang['EP_noclassfile'] = 'Fatale Fout: Class-Bestand bestaat niet: %s';

$_lang['EP_ex_undef'] = 'Ongespecificeerde EasyPoll Exception: %s';
$_lang['EP_db_error'] = 'Database fout. Een SQL Commando mislukte';
$_lang['EP_ex_invalidparam'] = 'Ongeldige Parameter aangedragen voor: \'%s\'';
?>