<?php
/**
 * ------------------------------------------------------------------------------
 * EasyPoll Snippet
 * ------------------------------------------------------------------------------
 * Another Poll Module, inspired by the Poll Module developped by garryn
 *
 * EasyPoll is free software; you can redistribute it and/or modify
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
 * ------------------------------------------------------------------------------
 * Read the bundled documentation.html for usage instructions.
 * ------------------------------------------------------------------------------
 *
 * @author banal, vanchelo <brezhnev.ivan@yahoo.com>
 * @version 0.3.4 <2014-09-23>
 */

// set up parameters
$config = array();
$config['lang'] = isset($lang) && preg_match('/^[a-z]{2,3}$/', $lang) ? $lang : 'en';
$config['easylang'] = isset($easylang) && preg_match('/^[a-z]{2,3}$/', $easylang) ? $easylang : $config['lang'];
$config['pollid'] = isset($pollid) ? intval($pollid) : false;
$config['onevote'] = isset($onevote) ? $onevote == true : false;
$config['useip'] = isset($useip) ? $useip == true : false;
$config['nojs'] = isset($nojs) ? $nojs == true : false;
$config['noajax'] = isset($noajax) ? $noajax == true : false;
$config['archive'] = isset($archive) ? $archive == true : false;
$config['votesorting'] = isset($votesorting) && preg_match('/^(Sorting|Votes)(\s(DESC|ASC))?$/i', $votesorting) ? $votesorting : 'Sorting ASC';
$config['skipfirst'] = isset($skipfirst) ? $skipfirst == true : false;
$config['css'] = isset($css) && $css !== '' ? $css : '/assets/snippets/EasyPoll/poll.css';
$config['identifier'] = isset($identifier) ? $identifier : 'easypoll';
$config['accuracy'] = isset($accuracy) ? intval($accuracy) : 1;
$config['tplPoll'] = isset($tplPoll) ? $tplPoll : false;
$config['tplVoteOuter'] = isset($tplVoteOuter) ? $tplVoteOuter : false;
$config['tplVote'] = isset($tplVote) ? $tplVote : false;
$config['tplResultOuter'] = isset($tplResultOuter) ? $tplResultOuter : false;
$config['tplResult'] = isset($tplResult) ? $tplResult : false;
$config['tplChoice'] = isset($tplChoice) ? $tplChoice : false;
$config['tplSubmitBtn'] = isset($tplSubmitBtn) ? $tplSubmitBtn : false;
$config['tplResultBtn'] = isset($tplResultBtn) ? $tplResultBtn : false;
$config['ovtime'] = isset($ovtime) ? intval($ovtime) : 608400;
$config['jscallback'] = isset($jscallback) ? $jscallback : false;
$config['customjs'] = isset($customjs) ? $customjs : false;
$config['showexception'] = isset($showexception) ? $showexception == true : false;

// set the base path
$path = $modx->config['base_path'] . 'assets/snippets/EasyPoll/';

// check if required files exist
$langfile = $path . 'lang/lang.' . $config['lang'] . '.php';
$classfile = $path . 'easypoll.class.php';

if (!file_exists($langfile)) {
    $modx->messageQuit('EasyPoll Snippet Error: Unable to locate language File for language: ' . $config['lang']);
    return;
}

if (!file_exists($classfile)) {
    $modx->messageQuit('EasyPoll Snippet Error: Unable to locate easypoll.class.php');
    return;
}

// include files
$_lang = array();
require $langfile;
require_once $classfile;

try {
    $handler = new EasyPoll($modx, $config, $_lang);

    return $handler->generateOutput();
} catch (Exception $e) {
    // only display the exception if we have a error code above 0. otherwise remain silent
    if ($e->getCode() > 0 || $config['showexception']) {
        // if we get a code above or equal to 128, we just exit
        if ($e->getCode() >= 128) {
            return $e->getMessage();
        } else {
            $modx->messageQuit('EasyPoll Snippet Error: ' . $e->getMessage());
        }
    }
}

return;
