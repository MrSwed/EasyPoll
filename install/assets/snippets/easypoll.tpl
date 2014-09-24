//<?php
/**
* EasyPoll
*
* Another Poll Module, inspired by the Poll Module developped by garryn
*
* @category    snippet
* @version     0.3.4
* @author      banal, vanchelo <brezhnev.ivan@yahoo.com>
* @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
* @internal    @properties
* @internal    @modx_category EasyPoll
* @internal    @properties &lang=Язык;text;ru &onevote=Один голос;text;1 &useip=Использовать IP;text;1
* @internal    @installset base, sample
*/

/**
 * Вставляем строку ниже после двоеточия на вкладке "Свойства" в поле "Параметры по умолчанию"
 * Settings: &lang=Язык;string;ru &onevote=Один голос;text;1 &useip=Использовать IP;text;1
 */
return require MODX_BASE_PATH . 'assets/snippets/EasyPoll/snippet.php';
