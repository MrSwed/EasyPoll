<?php
/**
 * ------------------------------------------------------------------------------
 * EasyPoll Vote Class
 * ------------------------------------------------------------------------------
 * EasyPoll Voting, loosely based on the poll manager by garryn
 *
 * Dependencies:
 * MODx 0.9.5, 0.9.6
 * 		these are the versions this snippet was developped for.
 * 		might work with other versions as well. not tested
 *
 * MooTools with XHR/Ajax module <http://mootools.net/>
 *
 * @author banal
 * @version 0.3.3 <2008-10-21>
 */

class EasyPoll {
	// configuration array
	private $config;
	// language specific strings array
	private $lang;
	// flag indicating if the user voted already
	private $voted;
	// the poll DB id
	private $pollid;
	// the language id
	private $langid;
	// flag if init is done
	private $isinit;
	// flag if in archive mode
	private $archive;

	// database tables
	private $tbl_poll;
	private $tbl_choice;
	private $tbl_ip;
	private $tbl_lang;
	private $tbl_trans;

	// templates
	private $templates;

	/**
	 * Constructor.
	 * Expects two arrays as parameters.
	 *
	 * @param $config configuration parameters, coming from the snippet
	 * @param $lang array containing language specific strings
	 */
	public function __construct(array &$config, array &$lang) {
		$this->config =& $config;
		$this->lang =& $lang;
		$this->isinit = false;
		$this->archive = $this->config['archive'] == true;
	}

	/**
	 * Generate the output.
	 * @return string the generated HTML Output.
	 */
	public function generateOutput(){
		global $modx;

		$this->init();

		// if in archive mode, return the archive..
		if($this->archive)
			return $this->generateArchive();

		// store a flag if this is the right poll... necessary for multiple poll handling
		$isThisPoll = (isset($_POST['idf']) && $_POST['idf'] == $this->config['identifier']);

		// check if we're dealing with a ajaxrequest here
		if(!$this->config['noajax'] && !empty($_POST['ajxrequest']) && $_POST['ajxrequest'] == 1){
			// we check if this really concerns us
			if(!$isThisPoll)
				return;

			// remove the parameter to not trigger an infinite loop
			unset($_POST['ajxrequest']);
			// return the generated output
			echo $this->generateOutput();
			exit();
		}

		// catch a vote
		if(isset($_POST['submit']) && $isThisPoll){
			// if this user has voted already, return message
			if($this->voted){
				$values = array('message' => $this->lang['alreadyvoted']);
				if($this->templates['tplError']['isfunction']){
					return call_user_func($this->templates['tplError']['value'], $values, 'tplError');
				} else {
					return $this->tplReplace($values, $this->templates['tplError']['value']);
				}
			}

			$success = $this->submitVote(intval($_POST['poll_choice']));
			if($success){
				// successfully voted. lock the user if necessary
				$this->lockUser();
				$this->voted = true;
			} else {
				$values = array('message' => $this->lang['error']);
				if($this->templates['tplError']['isfunction']){
					return call_user_func($this->templates['tplError']['value'], $values, 'tplError');
				} else {
					return $this->tplReplace($values, $this->templates['tplError']['value']);
				}
			}
		}

		// get the poll
		$query = '
		SELECT
			t.TextValue AS \'title\',
			(SELECT SUM(c.Votes) FROM '. $this->tbl_choice .' c WHERE c.idPoll = p.idPoll) AS \'votes\'
		FROM '. $this->tbl_poll .' p LEFT OUTER JOIN '. $this->tbl_trans .' t ON p.idPoll = t.idPoll
		WHERE t.idPoll='. $this->pollid .' AND t.idChoice = 0 AND t.idLang='. $this->langid;

		$rs = $modx->db->query($query);
		$row = $modx->db->getRow($rs);
		$title = $row['title'];
		$numvotes = $row['votes'];

		if($this->config['css'])
			$modx->regClientCSS($this->config['css']);


		$choicequery = '
		SELECT
			t.TextValue AS \'title\',
			c.Votes AS \'votes\',
			c.idChoice AS \'choiceid\'
		FROM '. $this->tbl_choice .' c LEFT OUTER JOIN '. $this->tbl_trans .' t ON c.idChoice = t.idChoice
		WHERE c.idPoll='. $this->pollid .' AND t.idLang='. $this->langid .' ORDER BY c.';
		
		// user has voted already or explicitly wants to see the results
		if( $this->voted || ($isThisPoll && isset($_GET['showresults'])) || isset($_POST['result']) ){
			$choicequery .= $this->config['votesorting'];
			
			$buf = '';
			$rs = $modx->db->query($choicequery);
			while($row = $modx->db->getRow($rs)){
				if($numvotes > 0){
					$perc = round(100 / $numvotes * $row['votes'], $this->config['accuracy']);
					$perc_int = (int)$perc;
				} else {
					$perc = $perc_int = 0;
				}

				$values = array(
					'answer' => $row['title'],
					'percent' => $perc,
					'percent_int' => $perc_int,
					'votes' => $row['votes']
				);

				if($this->templates['tplResult']['isfunction']){
					$buf .= call_user_func($this->templates['tplResult']['value'], $values, 'tplResult');
				} else {
					$buf .= $this->tplReplace($values, $this->templates['tplResult']['value']);
				}
			}

			//TODO: if user has not voted, display button to take him back to voting screen?
			$values = array(
				'question' => $title,
				'totalvotes' => $numvotes,
				'totaltext' => $this->lang['totalvotes'],
				'choices' => $buf
			);

			if($this->templates['tplResultOuter']['isfunction']){
				$buffer = call_user_func($this->templates['tplResultOuter']['value'], $values, 'tplResultOuter');
			} else {
				$buffer = $this->tplReplace($values, $this->templates['tplResultOuter']['value']);
			}

			return $buffer;
		}

		// request mootools unless specified otherwise
		if(!$this->config['nojs'])
			$modx->regClientStartupScript('manager/media/script/mootools/mootools.js');

		// request our helper class, only if ajax enabled
		if(!$this->config['noajax'])
			$modx->regClientStartupScript('assets/snippets/EasyPoll/script/EasyPollAjax.js');

		$url = $modx->makeUrl($modx->documentObject['id'],'','&showresults=1');
		$urlajax = $modx->makeUrl($modx->documentObject['id'],'','');

		$callback = $this->config['jscallback'] ? $this->config['jscallback'] : 'EasyPoll_DefaultCallback';

		$idf = $this->config['identifier'];
		$header = '
		<div id="'. $idf .'" class="easypoll"><form name="'. $idf .'form" id="'. $idf .'form" method="POST" action="' . $url . '">
		<fieldset><input type="hidden" id="'. $idf .'ajx" name="ajxrequest" value="0"/>
		<input type="hidden" name="pollid" value="'. $this->pollid .'"/><input type="hidden" name="idf" value="' . $idf .'"/>';
		
		$choicequery .= 'Sorting ASC';
		$rs = $modx->db->query($choicequery);
		$buf = '';
		while($row = $modx->db->getRow($rs)){
			$values = array(
				'answer' => htmlentities($row['title'], ENT_COMPAT, 'UTF-8'),
				'select' => '<input type="radio" name="poll_choice" value="' . $row['choiceid'] . '"/>'
			);

			if($this->templates['tplVote']['isfunction']){
				$buf .= call_user_func($this->templates['tplVote']['value'], $values, 'tplVote');
			} else {
				$buf .= $this->tplReplace($values, $this->templates['tplVote']['value']);
			}
		}

		$values = array(
			'question' => htmlentities($title, ENT_COMPAT, 'UTF-8'),
			'submit' => '<input type="submit" name="submit" class="pollbutton" value="'. $this->lang['vote']
						.'" id="'. $idf .'submit"/>',
			'results' => '<input type="submit" name="result" class="pollbutton" id="'. $idf .'result" value="'. $this->lang['results'] .'" />',
			'choices' => $buf
		);

		if($this->templates['tplVoteOuter']['isfunction']){
			$buffer = call_user_func($this->templates['tplVoteOuter']['value'], $values, 'tplVoteOuter');
		} else {
			$buffer = $this->tplReplace($values, $this->templates['tplVoteOuter']['value']);
		}
		$buffer = $header . $buffer . '</fieldset></form></div>';

		// request any external js file if needed
		if($this->config['customjs']){
			$match = array();
			if(preg_match('/^@CHUNK(:|\s)\s*(\w+)$/i', $this->config['customjs'], $match)){
				$js = $modx->getChunk($match[2]);
			} else {
				$js = $this->config['customjs'];
			}

			if(preg_match('/^<script/i', $js)){
				$buffer .= $js;
			} else {
				$modx->regClientStartupScript($js);
			}
		}

		if(!$this->config['noajax']){
			$buffer .= '
			<script type="text/javascript">
			// <!--
			var js' . $idf . ' = new EasyPollAjax("'. $idf .'", "'. $urlajax .'");
			js' . $idf . '.registerCallback(' . $callback . ');
			js' . $idf . '.registerButton("submit");
			js' . $idf . '.registerButton("result");
			// -->
			</script>
			';
		}
		return $buffer;
	}

	/**
	 * Generate a poll archive.
	 * This will simply output all past polls
	 * @return string the generated HTML
	 */
	private function generateArchive(){
		global $modx;

		// allow loading of css styles
		if($this->config['css'])
			$modx->regClientCSS($this->config['css']);


		$output = '';

		// get the polls. make sure we don't get any inactive, non-translated or polls without choices
		$query = '
		SELECT
			p.idPoll AS \'pollid\',
			t.TextValue AS \'title\',
			(SELECT SUM(c.Votes) FROM '. $this->tbl_choice .' c WHERE c.idPoll = p.idPoll) AS \'votes\'
			FROM '. $this->tbl_poll .' p LEFT OUTER JOIN '. $this->tbl_trans .' t ON p.idPoll = t.idPoll
		WHERE
			(SELECT COUNT(c.idPoll) FROM '. $this->tbl_choice .' c WHERE c.idPoll=p.idPoll) > 0 AND
			(SELECT COUNT(t.idPoll) FROM '. $this->tbl_trans .' t WHERE t.idPoll=p.idPoll AND t.idLang='. $this->langid .')
		    - (SELECT COUNT(c.idPoll)+1 FROM '. $this->tbl_choice .' c WHERE c.idPoll=p.idPoll) = 0
			AND p.isActive=1 AND t.idChoice = 0 AND p.StartDate <= NOW() AND t.idLang='. $this->langid .'
		ORDER BY p.StartDate DESC';

		$rs = $modx->db->query($query);
		$count = 0;
		while($row = $modx->db->getRow($rs)){
			$count++;
			if($count == 1 && $this->config['skipfirst'])
				continue;

			$title = $row['title'];
			$numvotes = $row['votes'];

			$choicequery = '
			SELECT
				t.TextValue AS \'title\',
				c.Votes AS \'votes\',
				c.idChoice AS \'choiceid\'
			FROM '. $this->tbl_choice .' c LEFT OUTER JOIN '. $this->tbl_trans .' t ON c.idChoice = t.idChoice
			WHERE c.idPoll='. $row['pollid'] .' AND t.idLang='. $this->langid .' ORDER BY c.' . $this->config['votesorting'];

			$buf = '';
			$rs2 = $modx->db->query($choicequery);
			while($row2 = $modx->db->getRow($rs2)){
				if($numvotes > 0){
					$perc = round(100 / $numvotes * $row2['votes'], $this->config['accuracy']);
					$perc_int = (int)$perc;
				} else {
					$perc = $perc_int = 0;
				}

				$values = array(
					'answer' => htmlentities($row2['title'], ENT_COMPAT, 'UTF-8'),
					'percent' => $perc,
					'percent_int' => $perc_int,
					'votes' => $row2['votes']
				);

				if($this->templates['tplResult']['isfunction']){
					$buf .= call_user_func($this->templates['tplResult']['value'], $values, 'tplResult');
				} else {
					$buf .= $this->tplReplace($values, $this->templates['tplResult']['value']);
				}
			}
			$values = array(
				'question' => htmlentities($title, ENT_COMPAT, 'UTF-8'),
				'totalvotes' => $numvotes,
				'totaltext' => $this->lang['totalvotes'],
				'choices' => $buf
			);

			if($this->templates['tplResultOuter']['isfunction']){
				$buffer = call_user_func($this->templates['tplResultOuter']['value'], $values, 'tplResultOuter');
			} else {
				$buffer = $this->tplReplace($values, $this->templates['tplResultOuter']['value']);
			}

			$output .= $buffer;
		}

		// only include js when there is any output
		if($count > 0 || ($this->config['skipfirst'] && $count > 1)){
			// request any external js file if needed
			if($this->config['customjs']){
				$match = array();
				if(preg_match('/^@CHUNK(:|\s)\s*(\w+)/i', $this->config['customjs'], $match)){
					$js = $modx->getChunk($match[2]);
				} else {
					$js = $this->config['customjs'];
				}

				if(preg_match('/^<script/i', $js)){
					$output .= $js;
				} else {
					$modx->regClientStartupScript($js);
				}
			}
		}

		return $output;
	}

	/*
	 * Initialize items
	 */
	private function init(){
		global $modx;

		if(!isset($modx))
			throw new Exception('Must run in MODx environment',1);

		if($this->isinit)
			return;

		$this->tbl_poll		= $modx->getFullTableName('ep_poll');
		$this->tbl_choice	= $modx->getFullTableName('ep_choice');
		$this->tbl_ip		= $modx->getFullTableName('ep_userip');
		$this->tbl_lang		= $modx->getFullTableName('ep_language');
		$this->tbl_trans	= $modx->getFullTableName('ep_translation');

		$this->templates = array();
		$this->setupTemplate('tplVoteOuter');
		$this->setupTemplate('tplVote');
		$this->setupTemplate('tplResultOuter');
		$this->setupTemplate('tplResult');
		$this->setupTemplate('tplError');

		$this->langid		= $this->getLangId();
		if(!$this->archive){
			$this->pollid = $this->getPollId();
			$this->voted = $this->getVotedStatus();
		}
		$this->isinit = true;
	}

	/**
	 * Initialize the requested template
	 * Fill the templates array
	 */
	private function setupTemplate($key){
		global $modx;

		if($this->config[$key]){
			$chunk = $this->config[$key];
			$match = array();
			if(preg_match('/^@FUNCTION(:|\s)\s*(\w+)/i', $chunk, $match)){
				if(!function_exists($match[2]))
					throw new Exception('Template handler ('. $key .') function does not exist. Function: ' . $match[2]);

				$this->templates[$key] = array('value' => $match[2], 'isfunction' => true);
			} else if(preg_match('/^@FUNCTIONCHUNK(:|\s)\s*(\w+)/i', $chunk, $match)){
				$content = $modx->getChunk($match[2]);
				if(!$content)
					throw new Exception('No chunk for @FUNCTIONCHUNK ' . $match[2]);

				$fmatch = array();
				// look if there is a function definition
				if(preg_match('/function\s+(\w+)/ims', $content, $fmatch)){
					if(!function_exists($fmatch[1])){
						// build the function
						if(eval($content) === false)
							throw new Exception('Errors in function definition: ' . $chunk);
					}
					$this->templates[$key] = array('value' => $fmatch[1], 'isfunction' => true );
				} else {
					throw new Exception('No function definition in: ' . $chunk);
				}

			} else {
				$this->templates[$key] = array('value' => $modx->getChunk($chunk), 'isfunction' => false);
			}
		} else {
			switch($key){
				case 'tplVoteOuter':
					$this->templates[$key] = array(
						'value' => '<div class="pollvotes"><h3>[+question+]</h3><ul>[+choices+]</ul>[+submit+] [+results+]</div>',
						'isfunction' => false
					);
					break;
				case 'tplResultOuter':
					$this->templates[$key] = array(
						'value' => '<div class="pollresults"><h3>[+question+]</h3><ul>[+choices+]</ul><p>[+totaltext+]: [+totalvotes+]</p></div>',
						'isfunction' => false
					);
					break;
				case 'tplVote':
					$this->templates[$key] = array(
						'value' => '<li>[+select+] [+answer+]</li>',
						'isfunction' => false
					);
					break;
				case 'tplResult':
					$this->templates[$key] = array(
						'value' => '<li><strong>[+answer+]</strong> ([+votes+] / [+percent+]%)<div class="easypoll_bar">' .
								   '<div class="easypoll_inner" style="width:[+percent_int+]%"></div></div></li>',
						'isfunction' => false
					);
					break;
				case 'tplError':
					$this->templates[$key] = array(
						'value' => '<div class="easypoll_error">[+message+]</div>',
						'isfunction' => false
					);
					break;
			}
		}
	}

	/**
	 * Lock the current user to prevent him from voting another time
	 */
	private function lockUser(){
		global $modx;

		if(!$this->config['onevote'])
			return;

		setcookie('EasyPoll' . $this->pollid, 'novote', time() + $this->config['ovtime'], '/');

		if($this->config['useip']){
			$ip = $this->getUserIp();
			$rs = $modx->db->insert(array('idPoll' => $this->pollid, 'ipAddress' => $ip), $this->tbl_ip);
		}
	}

	/**
	 * Submit a vote for a poll and store it into database
	 * @param $choice the choice to vote for
	 * @return true if the vote was stored, false if not
	 */
	private function submitVote($choice){
		global $modx;

		$choice = intval($choice);

		if(!$choice)
			return false;

		$query = 'UPDATE ' . $this->tbl_choice . ' SET Votes = Votes+1 WHERE idChoice=' . $choice . ' AND idPoll=' . $this->pollid;
		$result = $modx->db->query($query);

		return $result == true;
	}

	/**
	 * Return the poll id.
	 * Checks if the poll exist in the database and if it's active and inside
	 * timeframe.
	 * @return the poll id
	 * @throws Exception when poll is non-existant or the language is not ready
	 */
	private function getPollId(){
		global $modx;

		// we don't need to do this twice...
		if(isset($this->pollid))
			return $this->pollid;

		$tmpid = 0;

		if($this->config['pollid'] == false){
			$rs = $modx->db->select
			(
				'p.idPoll',
				$this->tbl_poll . ' p',
				'isActive=1 AND StartDate <= NOW() AND (EndDate = 0 || EndDate >= NOW())
				AND (SELECT COUNT(c.idPoll) FROM '. $this->tbl_choice .' c WHERE c.idPoll=p.idPoll) > 0',
				'p.StartDate DESC',
				'1'
			);

			//TODO: Make this translatable or customizable as well
			if($modx->db->getRecordCount($rs) == 0)
				throw new Exception('No polls available at this time.', 128);

			$row = $modx->db->getRow($rs);
			$tmpid = $row['idPoll'];
		} else {
			$tmpid = intval($this->config['pollid']);
			$rs = $modx->db->select
			(
				'p.idPoll',
				$this->tbl_poll . ' p',
				'idPoll=' . $tmpid . ' AND isActive=1 AND StartDate <= NOW() AND (EndDate = 0 || EndDate >= NOW())
				AND (SELECT COUNT(c.idPoll) FROM '. $this->tbl_choice .' c WHERE c.idPoll=p.idPoll) > 0'
			);

			if($modx->db->getRecordCount($rs) == 0)
				throw new Exception('The poll with id ' . $tmpid . ' is not available');
		}

		// if we're here, $tmpid will have a valid poll id! now we check if desired language is available
    	$query = '
    	SELECT (SELECT COUNT(t.idPoll) FROM '. $this->tbl_trans .' t WHERE t.idPoll='. $tmpid .' AND t.idLang='. $this->langid .')
    	- (SELECT COUNT(c.idPoll)+1 FROM '. $this->tbl_choice .' c WHERE c.idPoll='. $tmpid .') AS \'diff\'';

    	$rs = $modx->db->query($query);
    	$row = $modx->db->getRow($rs);
    	// diff must be 0, otherwise not all items are translated!
    	$diff = $row['diff'];
    	if($diff != 0)
    		throw new Exception('The language (' . $this->config['easylang'] . ') cannot be used yet, because not all items are translated!'.
    		' Please translate items using the EasyPoll Manager');

    	return $tmpid;
	}

	/**
	 * Get the language id
	 * @return the language id
	 */
	private function getLangId(){
		global $modx;

		if($this->langid)
			return $this->langid;

		$rs = $modx->db->select('idLang', $this->tbl_lang, 'LangShort=\'' . $this->config['easylang'] .'\'');
		if($modx->db->getRecordCount($rs) == 0)
			throw new Exception('The language (' . $this->config['easylang'] . ') specified in the snippet call is not defined!', 1);

		$row = $modx->db->getRow($rs);
		return $row['idLang'];
	}

	/**
	 * Get the flag if the user has already voted
	 * @return true if the user has voted already, false if not
	 */
	private function getVotedStatus(){
		global $modx;

		// no need to invesitgate further when onevote is disabled
		if(!$this->config['onevote'])
			return false;

		// status flag is already set. return
		if($this->voted === true)
			return true;

		// check the cookie for status
		if(isset($_COOKIE['EasyPoll' . $this->pollid]))
			return true;

		// if ip option is set, check for user ip
		if($this->config['useip']){
			$userip = $this->getUserIp();
			$rs = $modx->db->select('idPoll', $this->tbl_ip, 'idPoll=' . $this->pollid . ' AND ipAddress=\'' . $userip .'\'');
			if($modx->db->getRecordCount($rs) > 0)
				return true;
		}

		// done checking everything. must not have voted yet
		return false;
	}

	/**
	 * Replace all placeholders of tplString with the values
	 * from $fields
	 * @param $field array containing all key -> values to insert
	 * @param $tplString string the template string with placeholders
	 * @return string the template with filled placeholders
	 */
	private function tplReplace(array &$fields, $tplString){
		$buf = $tplString;
		foreach($fields as $k => $v){
			$buf = str_replace('[+'. $k .'+]', $v, $buf);
		}
		return $buf;
	}

	/**
	 * Get the users ip address. taken from the original snippet by garryn
	 * @return the user ip address
	 */
	private function getUserIp() {
		// This returns the True IP of the client calling the requested page
		// Checks to see if HTTP_X_FORWARDED_FOR
		// has a value then the client is operating via a proxy
		if ($_SERVER['HTTP_CLIENT_IP'] <> '') {
			$userIP = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($_SERVER['HTTP_X_FORWARDED_FOR'] <> '') {
			$userIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif ($_SERVER['HTTP_X_FORWARDED'] <> '') {
			$userIP = $_SERVER['HTTP_X_FORWARDED'];
		}
		elseif ($_SERVER['HTTP_FORWARDED_FOR'] <> '') {
			$userIP = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		elseif ($_SERVER['HTTP_FORWARDED_FOR'] <> '') {
			$userIP = $_SERVER['HTTP_FORWARDED_FOR'];
		} else {
			$userIP = $_SERVER['REMOTE_ADDR'];
		}
		// return the IP we've figured out:
		return $userIP;
	}
}
?>