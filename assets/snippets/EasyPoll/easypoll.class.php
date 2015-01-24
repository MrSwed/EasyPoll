<?php

/**
 * ------------------------------------------------------------------------------
 * EasyPoll Vote Class
 * ------------------------------------------------------------------------------
 * EasyPoll Voting, loosely based on the poll manager by garryn
 *
 * Dependencies:
 * MODx >=1.0.10
 *        these are the versions this snippet was developped for.
 *        might work with other versions as well. not tested
 *
 * jQuery with XHR/Ajax module <http://jquery.com/>
 *
 * @author banal, vanchelo <brezhnev.ivan@yahoo.com>
 * @version 0.3.4 <2014-09-23>
 */
class EasyPoll
{
    /**
     * @var DocumentParser
     */
    protected $modx;
    /**
     * @var array Configuration array
     */
    protected $config;
    /**
     * @var array Language specific strings array
     */
    protected $lang;
    /**
     * @var bool Flag indicating if the user voted already
     */
    protected $voted;
    /**
     * @var int The poll DB ID
     */
    protected $pollid;
    /**
     * @var int The language ID
     */
    protected $langid;
    /**
     * @var bool Flag if init is done
     */
    protected $isInit = false;
    /**
     * @var bool Flag if in archive mode
     */
    protected $archive;
    /**
     * @var string Polls table
     */
    protected $tbl_poll;
    /**
     * @var string Choices table
     */
    protected $tbl_choice;
    /**
     * @var string IPs table
     */
    protected $tbl_ip;
    /**
     * @var string Languages table
     */
    protected $tbl_lang;
    /**
     * @var string Translations table
     */
    protected $tbl_trans;
    /**
     * @var array Templates
     */
    protected $templates = array();

    /**
     * Constructor
     *
     * @param DocumentParser $modx
     * @param array $config Configuration parameters, coming from the snippet
     * @param array $lang Array containing language specific strings
     */
    public function __construct(DocumentParser & $modx, array & $config, array & $lang)
    {
        $this->modx =& $modx;
        $this->config =& $config;
        $this->lang =& $lang;
        $this->archive = $this->config['archive'] == true;
    }

    /**
     * Generate the output.
     * @return string the generated HTML Output.
     */
    public function generateOutput()
    {
        $this->init();

        // if in archive mode, return the archive..
        if ($this->archive) {
            return $this->generateArchive();
        }

        /** @var string $idf Poll Identifier */
        $idf = $this->config['identifier'];

        // store a flag if this is the right poll... necessary for multiple poll handling
        $isThisPoll = (isset($_POST['idf']) && $_POST['idf'] == $idf);

        // check if we're dealing with a ajaxrequest here
        if (!$this->config['noajax'] && !empty($_POST['ajxrequest']) && $_POST['ajxrequest'] == 1) {
            // we check if this really concerns us
            if (!$isThisPoll) {
                return '';
            }

            // remove the parameter to not trigger an infinite loop
            unset($_POST['ajxrequest']);
            // return the generated output
            echo $this->generateOutput();
            exit();
        }

        // catch a vote
        if (isset($_POST['submit']) && $isThisPoll) {
            // if this user has voted already, return message
            if ($this->voted) {
                return $this->renderTemplate('tplError', array(
                    'message' => $this->lang['alreadyvoted']
                ));
            }

            $success = $this->submitVote(intval($_POST['poll_choice']));
            if ($success) {
                // successfully voted. lock the user if necessary
                $this->lockUser();
                $this->voted = true;
            } else {
                return $this->renderTemplate('tplError', array(
                    'message' => $this->lang['error']
                ));
            }
        }

        // get the poll
        $query = "SELECT t.TextValue AS 'title',
			(SELECT SUM(c.Votes) FROM {$this->tbl_choice} c WHERE c.idPoll = p.idPoll) AS 'votes'
		FROM {$this->tbl_poll} p LEFT OUTER JOIN {$this->tbl_trans} t ON p.idPoll = t.idPoll
		WHERE t.idPoll = {$this->pollid} AND t.idChoice = 0 AND t.idLang = {$this->langid}";

        $rs = $this->modx->db->query($query);
        $row = $this->modx->db->getRow($rs);
        $title = $row['title'];
        $numvotes = $row['votes'];

        if ($this->config['css']) {
            $this->modx->regClientCSS($this->config['css']);
        }

        $choicequery = "SELECT t.TextValue AS 'title', c.Votes AS 'votes', c.idChoice AS 'choiceid'
		FROM {$this->tbl_choice} c LEFT OUTER JOIN {$this->tbl_trans} t ON c.idChoice = t.idChoice
		WHERE c.idPoll = {$this->pollid} AND t.idLang = {$this->langid} ORDER BY c.";

        // user has voted already or explicitly wants to see the results
        if ($this->voted || ($isThisPoll && isset($_GET['showresults'])) || isset($_POST['result'])) {
            $choicequery .= $this->config['votesorting'];

            $choices = '';
            $rs = $this->modx->db->query($choicequery);
            while ($row = $this->modx->db->getRow($rs)) {
                if ($numvotes > 0) {
                    $perc = round(100 / $numvotes * $row['votes'], $this->config['accuracy']);
                    $perc_int = (int) $perc;
                } else {
                    $perc = $perc_int = 0;
                }

                $choices .= $this->renderTemplate('tplResult', array(
                    'answer' => $row['title'],
                    'percent' => $perc,
                    'percent_int' => $perc_int,
                    'votes' => $row['votes']
                ));
            }

            //TODO: if user has not voted, display button to take him back to voting screen?
            return $this->renderTemplate('tplResultOuter', array(
                'question' => $title,
                'totalvotes' => $numvotes,
                'totaltext' => $this->lang['totalvotes'],
                'choices' => $choices,
                'idf' => $idf
            ));
        }

        // request jQuery unless specified otherwise
        if (!$this->config['nojs']) {
            $this->modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js');
        }

        // request our helper class, only if ajax enabled
        if (!$this->config['noajax']) {
            $this->modx->regClientStartupScript('assets/snippets/EasyPoll/script/EasyPollAjax.js');
        }

        $url = $this->modx->makeUrl($this->modx->documentObject['id'], '', '&showresults=1');
        $urlajax = $this->modx->makeUrl($this->modx->documentObject['id'], '', '');
        $callback = $this->config['jscallback'] ? $this->config['jscallback'] : 'EasyPoll_DefaultCallback';
        $choicequery .= 'Sorting ASC';
        $choices = '';

        $rs = $this->modx->db->query($choicequery);
        while ($row = $this->modx->db->getRow($rs)) {
            $choices .= $this->renderTemplate('tplVote', array(
                'choiceid' => $row['choiceid'],
                'answer' => $this->entity($row['title']),
                'select' => $this->renderTemplate('tplChoice', $row),
            ));
        }

        $inner = $this->renderTemplate('tplVoteOuter', array(
            'question' => $this->entity($title),
            'submit' => $this->renderTemplate('tplSubmitBtn', array(
                'value' => $this->lang['vote'],
                'idf' => $idf
            )),
            'results' => $this->renderTemplate('tplResultBtn', array(
                'value' => $this->lang['results'],
                'idf' => $idf
            )),
            'choices' => $choices,
            'totalvotes' => $numvotes,
            'totaltext' => $this->lang['totalvotes'],
        ));

        $js = '';
        // request any external js file if needed
        if ($this->config['customjs']) {
            $match = array();
            if (preg_match('/^@CHUNK(:|\s)\s*(\w+)$/i', $this->config['customjs'], $match)) {
                $js = $this->modx->getChunk($match[2]);
            } else {
                $js = $this->config['customjs'];
            }

            if (preg_match('/^<script/i', $js)) {
                $js .= $js;
            } else {
                $this->modx->regClientStartupScript($js);
            }
        }

        if (!$this->config['noajax']) {
            $js .= '
			<script type="text/javascript">
			// <!--
			var js' . $idf . ' = new EasyPollAjax("' . $idf . '", "' . $urlajax . '");
			js' . $idf . '.registerCallback(' . $callback . ');
			js' . $idf . '.registerButton("submit");
			js' . $idf . '.registerButton("result");
			// -->
			</script>
			';
        }

        return $this->renderTemplate('tplPoll', array(
            'idf' => $idf,
            'pollid' => $this->pollid,
            'url' => $url,
            'inner' => $inner,
            'js' => $js,
        ));
    }

    /**
     * Generate a poll archive.
     * This will simply output all past polls
     *
     * @return string the generated HTML
     */
    protected function generateArchive()
    {
        // allow loading of css styles
        if ($this->config['css']) {
            $this->modx->regClientCSS($this->config['css']);
        }

        $output = '';

        // get the polls. make sure we don't get any inactive, non-translated or polls without choices
        $query = '
		SELECT
			p.idPoll AS \'pollid\',
			t.TextValue AS \'title\',
			(SELECT SUM(c.Votes) FROM ' . $this->tbl_choice . ' c WHERE c.idPoll = p.idPoll) AS \'votes\'
			FROM ' . $this->tbl_poll . ' p LEFT OUTER JOIN ' . $this->tbl_trans . ' t ON p.idPoll = t.idPoll
		WHERE
			(SELECT COUNT(c.idPoll) FROM ' . $this->tbl_choice . ' c WHERE c.idPoll=p.idPoll) > 0 AND
			(SELECT COUNT(t.idPoll) FROM ' . $this->tbl_trans . ' t WHERE t.idPoll=p.idPoll AND t.idLang=' . $this->langid . ')
		    - (SELECT COUNT(c.idPoll)+1 FROM ' . $this->tbl_choice . ' c WHERE c.idPoll=p.idPoll) = 0
			AND p.isActive=1 AND t.idChoice = 0 AND p.StartDate <= NOW() AND t.idLang=' . $this->langid . '
		ORDER BY p.StartDate DESC';

        $rs = $this->modx->db->query($query);
        $count = 0;
        while ($row = $this->modx->db->getRow($rs)) {
            $count++;
            if ($count == 1 && $this->config['skipfirst']) {
                continue;
            }

            $title = $row['title'];
            $numvotes = $row['votes'];

            $choicequery = '
			SELECT
				t.TextValue AS \'title\',
				c.Votes AS \'votes\',
				c.idChoice AS \'choiceid\'
			FROM ' . $this->tbl_choice . ' c LEFT OUTER JOIN ' . $this->tbl_trans . ' t ON c.idChoice = t.idChoice
			WHERE c.idPoll=' . $row['pollid'] . ' AND t.idLang=' . $this->langid . ' ORDER BY c.' . $this->config['votesorting'];

            $choices = '';
            $rs2 = $this->modx->db->query($choicequery);
            while ($row2 = $this->modx->db->getRow($rs2)) {
                if ($numvotes > 0) {
                    $perc = round(100 / $numvotes * $row2['votes'], $this->config['accuracy']);
                    $perc_int = (int) $perc;
                } else {
                    $perc = $perc_int = 0;
                }

                $choices .= $this->renderTemplate('tplResult', array(
                    'answer' => $this->entity($row2['title']),
                    'percent' => $perc,
                    'percent_int' => $perc_int,
                    'votes' => $row2['votes']
                ));
            }

            $buffer = $this->renderTemplate('tplResultOuter', array(
                'question' => $this->entity($title),
                'totalvotes' => $numvotes,
                'totaltext' => $this->lang['totalvotes'],
                'choices' => $choices
            ));

            $output .= $buffer;
        }

        // only include js when there is any output
        if ($count > 0 || ($this->config['skipfirst'] && $count > 1)) {
            // request any external js file if needed
            if ($this->config['customjs']) {
                $match = array();
                if (preg_match('/^@CHUNK(:|\s)\s*(\w+)/i', $this->config['customjs'], $match)) {
                    $js = $this->modx->getChunk($match[2]);
                } else {
                    $js = $this->config['customjs'];
                }

                if (preg_match('/^<script/i', $js)) {
                    $output .= $js;
                } else {
                    $this->modx->regClientStartupScript($js);
                }
            }
        }

        return $output;
    }

    /**
     * Initialize items
     *
     * @throws Exception
     */
    protected function init()
    {
        if ($this->isInit) return;

        $this->tbl_poll = $this->modx->getFullTableName('ep_poll');
        $this->tbl_choice = $this->modx->getFullTableName('ep_choice');
        $this->tbl_ip = $this->modx->getFullTableName('ep_userip');
        $this->tbl_lang = $this->modx->getFullTableName('ep_language');
        $this->tbl_trans = $this->modx->getFullTableName('ep_translation');

        $this->templates = array();
        $this->setupTemplate('tplPoll');
        $this->setupTemplate('tplVoteOuter');
        $this->setupTemplate('tplVote');
        $this->setupTemplate('tplResultOuter');
        $this->setupTemplate('tplResult');
        $this->setupTemplate('tplError');
        $this->setupTemplate('tplChoice');
        $this->setupTemplate('tplSubmitBtn');
        $this->setupTemplate('tplResultBtn');

        $this->langid = $this->getLangId();
        if (!$this->archive) {
            $this->pollid = $this->getPollId();
            $this->voted = $this->getVotedStatus();
        }

        $this->isInit = true;
    }

    /**
     * Initialize the requested template. Fill the templates array
     *
     * @param string $key
     *
     * @throws Exception
     */
    protected function setupTemplate($key)
    {
        if ($this->config[$key]) {
            $chunk = $this->config[$key];
            $match = array();
            if (preg_match('/^@FUNCTION(:|\s)\s*(\w+)/i', $chunk, $match)) {
                if (!function_exists($match[2])) {
                    throw new Exception("Template handler ({$key}) function does not exist. Function: {$match[2]}");
                }

                $this->templates[$key] = array(
                    'value' => $match[2],
                    'isfunction' => true
                );
            } elseif (preg_match('/^@FUNCTIONCHUNK(:|\s)\s*(\w+)/i', $chunk, $match)) {
                $content = $this->modx->getChunk($match[2]);
                if (!$content) {
                    throw new Exception("No chunk for @FUNCTIONCHUNK {$match[2]}");
                }

                $fmatch = array();
                // look if there is a function definition
                if (preg_match('/function\s+(\w+)/ims', $content, $fmatch)) {
                    if (!function_exists($fmatch[1])) {
                        // build the function
                        if (eval($content) === false) {
                            throw new Exception("Errors in function definition: {$chunk}");
                        }
                    }

                    $this->templates[$key] = array(
                        'value' => $fmatch[1],
                        'isfunction' => true
                    );
                } else {
                    throw new Exception("No function definition in: {$chunk}");
                }

            } else {
                $this->templates[$key] = array(
                    'value' => $this->modx->getChunk($chunk),
                    'isfunction' => false
                );
            }
        } else {
            switch ($key) {
                case 'tplVoteOuter':
                    $this->templates[$key] = array(
                        'value' => '<div class="pollvotes"><h3>[+question+]</h3><ul>[+choices+]</ul>[+submit+] [+results+]<span class="votes">[+totaltext+]: <strong>[+totalvotes+]</strong></span></div>',
                        'isfunction' => false
                    );
                    break;
                case 'tplResultOuter':
                    $this->templates[$key] = array(
                        'value' => '<div id="[+idf+]" class="easypoll pollresults"><h3>[+question+]</h3><ul>[+choices+]</ul><p>[+totaltext+]: <strong>[+totalvotes+]</strong></p></div>',
                        'isfunction' => false
                    );
                    break;
                case 'tplVote':
                    $this->templates[$key] = array(
                        'value' => '<li><label>[+select+] <span>[+answer+]</span></label></li>',
                        'isfunction' => false
                    );
                    break;
                case 'tplResult':
                    $this->templates[$key] = array(
                        'value' =>
                            '<li>' .
                                '<div class="answer"><strong>[+answer+]</strong> ([+percent+]%)</div>' .
                                '<div class="easypoll_bar">' .
                                    '<div class="easypoll_inner" style="width:[+percent_int+]%"></div>' .
                                    '<div class="easypoll_count">[+votes+]</div>' .
                                '</div>' .
                            '</li>',
                        'isfunction' => false
                    );
                    break;
                case 'tplError':
                    $this->templates[$key] = array(
                        'value' => '<div class="easypoll_error">[+message+]</div>',
                        'isfunction' => false
                    );
                    break;
                case 'tplChoice':
                    $this->templates[$key] = array(
                        'value' => '<input type="radio" class="easypoll_choice easypoll_choice_[+choiceid+]" name="poll_choice" value="[+choiceid+]"/>',
                        'isfunction' => false
                    );
                    break;
                case 'tplSubmitBtn':
                    $this->templates[$key] = array(
                        'value' => '<input type="submit" name="submit" class="pollbutton" id="[+idf+]submit" value="[+value+]" />',
                        'isfunction' => false
                    );
                    break;
                case 'tplResultBtn':
                    $this->templates[$key] = array(
                        'value' => '<input type="submit" name="result" class="pollbutton" id="[+idf+]result" value="[+value+]" />',
                        'isfunction' => false
                    );
                    break;
                case 'tplPoll':
                    $this->templates[$key] = array(
                        'value' =>
                            '<div id="[+idf+]" class="easypoll">' .
                            '<form name="[+idf+]form" id="[+idf+]form" method="POST" action="[+url+]">' .
                                '<fieldset>' .
                                    '<input type="hidden" id="[+idf+]ajx" name="ajxrequest" value="0"/>' .
                                    '<input type="hidden" name="pollid" value="[+pollid+]"/>' .
                                    '<input type="hidden" name="idf" value="[+idf+]"/>' .
                                    '[+inner+]' .
                                '</fieldset>' .
                            '</form>' .
                            '[+js+]' .
                            '</div>',
                        'isfunction' => false
                    );
                    break;
            }
        }
    }

    /**
     * Lock the current user to prevent him from voting another time
     */
    protected function lockUser()
    {
        if (!$this->config['onevote']) {
            return;
        }

        setcookie('EasyPoll' . $this->pollid, 'novote', time() + $this->config['ovtime'], '/');

        if ($this->config['useip']) {
            $ip = $this->getUserIp();
            $this->modx->db->insert(array(
                'idPoll' => $this->pollid,
                'ipAddress' => $ip
            ), $this->tbl_ip);
        }
    }

    /**
     * Submit a vote for a poll and store it into database
     *
     * @param int $choice The choice to vote for
     *
     * @return bool True If the vote was stored, false if not
     */
    protected function submitVote($choice)
    {
        if (!$choice = (int) $choice) {
            return false;
        }

        $query = 'UPDATE ' . $this->tbl_choice . ' SET Votes = Votes+1 WHERE idChoice=' . $choice . ' AND idPoll=' . $this->pollid;
        $result = $this->modx->db->query($query);

        return $result == true;
    }

    /**
     * Return the Poll ID
     * Checks if the poll exist in the database and if it's active and inside timeframe
     *
     * @return int The poll id
     *
     * @throws Exception when poll is non-existant or the language is not ready
     */
    protected function getPollId()
    {
        // we don't need to do this twice...
        if (isset($this->pollid)) {
            return $this->pollid;
        }

        $tmpid = 0;

        if ($this->config['pollid'] == false) {
            $rs = $this->modx->db->select(
                'p.idPoll',
                $this->tbl_poll . ' p',
                'isActive=1 AND StartDate <= NOW() AND (EndDate = 0 || EndDate >= NOW())
				AND (SELECT COUNT(c.idPoll) FROM ' . $this->tbl_choice . ' c WHERE c.idPoll=p.idPoll) > 0',
                'p.StartDate DESC',
                '1'
            );

            //TODO: Make this translatable or customizable as well
            if ($this->modx->db->getRecordCount($rs) == 0) {
                throw new Exception('No polls available at this time.', 128);
            }

            $row = $this->modx->db->getRow($rs);
            $tmpid = $row['idPoll'];
        } else {
            $tmpid = intval($this->config['pollid']);
            $rs = $this->modx->db->select(
                'p.idPoll',
                $this->tbl_poll . ' p',
                'idPoll=' . $tmpid . ' AND isActive=1 AND StartDate <= NOW() AND (EndDate = 0 || EndDate >= NOW())
				AND (SELECT COUNT(c.idPoll) FROM ' . $this->tbl_choice . ' c WHERE c.idPoll=p.idPoll) > 0'
            );

            if ($this->modx->db->getRecordCount($rs) == 0)
                throw new Exception("The poll with id {$tmpid} is not available");
        }

        // if we're here, $tmpid will have a valid poll id! now we check if desired language is available
        $query = '
    	SELECT (SELECT COUNT(t.idPoll) FROM ' . $this->tbl_trans . ' t WHERE t.idPoll=' . $tmpid . ' AND t.idLang=' . $this->langid . ')
    	- (SELECT COUNT(c.idPoll)+1 FROM ' . $this->tbl_choice . ' c WHERE c.idPoll=' . $tmpid . ') AS \'diff\'';

        $rs = $this->modx->db->query($query);
        $row = $this->modx->db->getRow($rs);
        // diff must be 0, otherwise not all items are translated!
        $diff = $row['diff'];
        if ($diff != 0) {
            throw new Exception('The language (' . $this->config['easylang'] . ') cannot be used yet, because not all items are translated! Please translate items using the EasyPoll Manager');
        }

        return $tmpid;
    }

    /**
     * Get the language id
     *
     * @return int The language id
     *
     * @throws Exception
     */
    protected function getLangId()
    {
        if ($this->langid) {
            return $this->langid;
        }

        $rs = $this->modx->db->select('idLang', $this->tbl_lang, "LangShort = '{$this->config['easylang']}'");
        if ($this->modx->db->getRecordCount($rs) == 0) {
            throw new Exception("The language ({$this->config['easylang']}) specified in the snippet call is not defined!", 1);
        }

        $row = $this->modx->db->getRow($rs);

        return $row['idLang'];
    }

    /**
     * Get the flag if the user has already voted
     *
     * @return bool True if the user has voted already, False if not
     */
    protected function getVotedStatus()
    {
        // no need to invesitgate further when onevote is disabled
        if (!$this->config['onevote']) {
            return false;
        }

        // status flag is already set. return
        if ($this->voted === true) {
            return true;
        }

        // check the cookie for status
        if (isset($_COOKIE["EasyPoll{$this->pollid}"])) {
            return true;
        }

        // if ip option is set, check for user ip
        if ($this->config['useip']) {
            $userip = $this->getUserIp();
            $rs = $this->modx->db->select('idPoll', $this->tbl_ip, "idPoll = {$this->pollid} AND ipAddress='{$userip}'");
            if ($this->modx->db->getRecordCount($rs) > 0) {
                return true;
            }
        }

        // done checking everything. must not have voted yet
        return false;
    }

    /**
     * Replace all placeholders of tplString with the values from $fields
     *
     * @param array $fields array containing all key -> values to insert
     * @param string $tplString string the template string with placeholders
     *
     * @return string the template with filled placeholders
     */
    protected function tplReplace(array & $fields, $tplString)
    {
        $buf = $tplString;
        foreach ($fields as $k => $v) {
            $buf = str_replace('[+' . $k . '+]', $v, $buf);
        }
        return $buf;
    }

    /**
     * Get the users ip address. Taken from the original snippet by garryn
     *
     * @return string The user ip address
     */
    protected function getUserIp()
    {
        // This returns the True IP of the client calling the requested page
        // Checks to see if HTTP_X_FORWARDED_FOR
        // has a value then the client is operating via a proxy
        if ($_SERVER['HTTP_CLIENT_IP'] <> '') {
            $userIP = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($_SERVER['HTTP_X_FORWARDED_FOR'] <> '') {
            $userIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_X_FORWARDED'] <> '') {
            $userIP = $_SERVER['HTTP_X_FORWARDED'];
        } elseif ($_SERVER['HTTP_FORWARDED_FOR'] <> '') {
            $userIP = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_FORWARDED_FOR'] <> '') {
            $userIP = $_SERVER['HTTP_FORWARDED_FOR'];
        } else {
            $userIP = $_SERVER['REMOTE_ADDR'];
        }

        // return the IP we've figured out:
        return $userIP;
    }

    protected function entity($string)
    {
        return htmlentities($string, ENT_COMPAT, 'UTF-8');
    }

    protected function renderTemplate($tpl, array $values = array())
    {
        if (!isset($this->templates[$tpl])) return '';

        $template =& $this->templates[$tpl];

        if ($template['isfunction']) {
            return call_user_func($template['value'], $values, $template);
        } else {
            return $this->tplReplace($values, $template['value']);
        }
    }
}
