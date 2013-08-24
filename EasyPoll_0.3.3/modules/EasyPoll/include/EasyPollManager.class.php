<?php
/**
 * ------------------------------------------------------------------------------
 * Easy Poll Manager
 * ------------------------------------------------------------------------------
 * Easy Poll Manager Class
 * This class encapsulates the logic of the EasyPoll Module.
 * This class is implemented as a singleton.
 *
 * Dependencies/Requirements:
 * - MODx 0.9.5, 0.9.6 ... others to be tested
 * - PHP Version 5 or greater
 * - MySQL Version 4.1 or better
 *
 * @author banal
 * @version 0.3 <2008-02-19>
 */

class EasyPollManager
{
	// the Singleton Object
	private static $singleton;

	const TPOLL = 'ep_poll';
	const TCHOICE = 'ep_choice';
	const TLANG = 'ep_language';
	const TUSER = 'ep_userip';
	const TTRANS = 'ep_translation';


	/** ************************************************************************
	 * Private, constructor. Prevents external construction of Objects
	 */
	private function __construct() {}

	/** ************************************************************************
     * Override the clone Method and throw Exception
     * if somebody tries to clone the singleton
     */
    public function __clone(){
    	throw new Exception('Cloning not allowed on singletons');
    }

    /** ************************************************************************
     * insert or update language table
     * @param $id		the id to update or false for insert
     * @param $short	short language string (2-3 letters)
     * @param $long		long version of language name
     * @return true on success, false on failure
     */
    public function insertLanguage($id, $short, $long){
    	global $modx;

    	$short = strtolower($short);
    	if(!preg_match('/^[a-z]{1,3}$/', $short))
    		throw new EasyPollException(
    			'Invalid param in ' . __METHOD__,
    			'EP_ex_invalidparam',
    			'EP_lang_short');

    	$long = trim($modx->db->escape($long));
    	$table = $modx->db->config['table_prefix'] . self::TLANG;

    	$id = intval($id);
    	$result = false;
    	$fields = array('LangShort' => $short, 'LangName' => $long);
    	if($id <= 0){
    		$result = $modx->db->insert($fields, $table);
    	} else {
    		$result = $modx->db->update($fields, $table, 'idLang=' . $id);
    	}

    	return $result == true;
    }

    /** ************************************************************************
     * get all languages as array
     * @return array containing all values
     */
    public function getLanguages(){
    	global $modx;
    	$output = array();
    	$table = $modx->db->config['table_prefix'] . self::TLANG;

    	$result = $modx->db->select
    	(
    		'idLang AS \'id\', LangShort AS \'short\', LangName AS \'long\'',
    		$table,
    		'',
    		'LangShort ASC'
    	);

    	while($row = $modx->db->getRow($result)){
    		$output[] = $row;
    	}

    	return $output;
    }

    /** ************************************************************************
     * get a language by specifying the id
     * @return array with id, short and long name
     */
	public function getLangById($id){
    	global $modx;

    	$table = $modx->db->config['table_prefix'] . self::TLANG;
    	$id = intval($id);

    	$result = $modx->db->select
    	(
    		'idLang AS \'id\', LangShort AS \'short\', LangName AS \'long\'',
    		$table,
    		'idLang=' . $id
    	);

    	if($modx->db->getRecordCount($result) > 0)
    		return $modx->db->getRow($result);

    	return false;
    }

    /** ************************************************************************
     * delete a language from the db
     * @return true when successful
     */
    public function deleteLanguage($id){
    	global $modx;
    	$tlang = $modx->db->config['table_prefix'] . self::TLANG;
    	$ttrans = $modx->db->config['table_prefix'] . self::TTRANS;

    	$id = intval($id);
    	$modx->db->query('SET AUTOCOMMIT=0;');
		$modx->db->query('START TRANSACTION;');

    	$rs1 = $modx->db->delete($tlang, 'idLang=' . $id);
    	$rs2 = $modx->db->delete($ttrans, 'idLang=' . $id);

    	$result = true;
    	if($rs1 && $rs2){
    		$modx->db->query('COMMIT;');
    	} else {
    		$modx->db->query('ROLLBACK;');
    		$result = false;
    	}
    	$modx->db->query('SET AUTOCOMMIT=1;');
    	return $result;
    }

    /** ************************************************************************
     * insert or update a poll
     * @param $id			the id to update or false for insert
     * @param $title		the polls internal title. must not be empty
     * @param $translation	associative array containing the language id
     * 						and the translated string for every language.
     * 						schema: langid => translation
     * @param $isActive		set the poll to be active
     * @param $startDate	starting date for the poll or false
     * @param $endDate		ending date for the poll or false
     * @return true on success, false on failure
     */
    public function insertPoll(
    	$id, $title, array &$translation, $isActive = false,
    	$startDate = false, $endDate = false
    	)
    {
    	global $modx;

    	$table = $modx->db->config['table_prefix'] . self::TPOLL;

    	$id = intval($id);
    	$title = trim($modx->db->escape($title));

    	if($title == '')
    		throw new EasyPollException(
				'Invalid param in ' . __METHOD__,
    			'EP_ex_invalidparam',
    			'EP_poll_title');

    	$isActive = $isActive == true ? 1 : 0;
    	$stDate = $startDate	== false ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($startDate));
    	$enDate = $endDate		== false ? 0 : date('Y-m-d H:i:s', strtotime($endDate));

    	$modx->db->query('SET AUTOCOMMIT=0;');
		$modx->db->query('START TRANSACTION;');

		$fields = array('Title' => $title, 'isActive' => $isActive, 'StartDate' => $stDate, 'EndDate' => $enDate);
		$errors = false;
		if($id > 0){
			$result = $modx->db->update($fields, $table, 'idPoll=' . $id);
		} else {
			$result = $modx->db->insert($fields, $table);
			$id = $result;
		}

		if(!$result)
			$errors = true;

		if(!$errors){
			foreach($translation as $key => $val){
				$res = $this->insertTranslation($id, false, $key, $val);
				if(!$res){
					$errors = true;
					break;
				}
			}

			if($errors){
				$modx->db->query('ROLLBACK;');
			} else {
				$modx->db->query('COMMIT;');
			}
		} else {
			$modx->db->query('ROLLBACK;');
		}

		$modx->db->query('SET AUTOCOMMIT=1;');


		if($errors)
			throw new EasyPollException(
				'SQL insertion or update failed at: ' . __METHOD__,
				'EP_db_error',
				'EP_tab_polls'
			);

		return true;
    }

	/** ************************************************************************
     * get all polls as array
     * @param $dateformat = format string for date
     * @return array containing all values
     * 			id = the poll id
     * 			title = the poll internal title
     * 			sdate = the startdate or NULL
     * 			edate = the enddate or NULL
     * 			active = either 1 or 0 if the poll is set active or inactive
     * 			translate = the number of items that still need to be translated
     * 						for this poll entry. 0 = all is translated
     * 			votes = number of votes for this poll
     */
    public function getPolls($dateformat = '%Y-%m-%d'){
    	global $modx;
    	$output = array();

    	if(preg_match("/[\n'\"]/", $dateformat)){
    		throw new EasyPollException(
    			'Invalid characters in date format: ' . __METHOD__,
    			'EP_ex_undef',
    			'EP_tab_polls'
    		);
    	}

    	$id = intval($id);

    	$tblP = $modx->db->config['table_prefix'] . self::TPOLL;
    	$tblT = $modx->db->config['table_prefix'] . self::TTRANS;
    	$tblC = $modx->db->config['table_prefix'] . self::TCHOICE;
    	$tblL = $modx->db->config['table_prefix'] . self::TLANG;

    	$query = "
    	SELECT
			p.idPoll AS 'id',
			p.Title AS 'title',
			IF(p.StartDate > 0, DATE_FORMAT(p.StartDate, '$dateformat'), '-') AS 'sdate',
			IF(p.EndDate > 0, DATE_FORMAT(p.EndDate, '$dateformat'), '-') AS 'edate',
			p.isActive AS 'active',
			(SELECT COUNT( ln.idLang ) FROM $tblL ln) *
			(SELECT COUNT(ch.idChoice)+1 FROM $tblC ch WHERE ch.idPoll = p.idPoll)
			- (SELECT COUNT(t.idPoll) FROM $tblT t WHERE t.idPoll = p.idPoll)
			AS 'translate',
			(SELECT SUM(c.Votes) FROM $tblC c WHERE c.idPoll = p.idPoll) AS 'votes',
			(SELECT COUNT(c.idChoice) FROM $tblC c WHERE c.idPoll = p.idPoll) AS 'choices'
		FROM $tblP p ORDER BY p.StartDate DESC";

    	$result = $modx->db->query($query);
    	while($row = $modx->db->getRow($result)){
    		$output[] = $row;
    	}

    	return $output;
    }

    /** ************************************************************************
     * get a poll by specifying the id
     * @param $id the poll id
     * @param $dateformat the format for the date string
     * @return array with id, title, sdate, edate and active or false upon failure
     */
    public function getPollById($id, $dateformat = '%Y-%m-%d'){
    	global $modx;

    	if(preg_match("/[\n'\"]/", $dateformat)){
    		throw new EasyPollException(
    			'Invalid characters in date format: ' . __METHOD__,
    			'EP_ex_undef',
    			'EP_tab_polls'
    		);
    	}

    	$table = $modx->db->config['table_prefix'] . self::TPOLL;
    	$id = intval($id);

    	$result = $modx->db->select
    	(	"idPoll AS 'id', Title AS 'title', IF(StartDate > 0, DATE_FORMAT(StartDate, '$dateformat'), '') AS 'sdate',
    		IF(EndDate > 0, DATE_FORMAT(EndDate, '$dateformat'), '') AS 'edate', isActive AS 'active'",
    		$table, 'idPoll=' .$id
    	);

    	if($modx->db->getRecordCount($result) > 0)
    		return $modx->db->getRow($result);

    	return false;
    }

	/** ************************************************************************
     * delete a poll from the db
     * @return true when successful
     */
    public function deletePoll($id){
    	global $modx;

    	$id = intval($id);

    	$tpoll = $modx->db->config['table_prefix'] . self::TPOLL;
    	$tchoice = $modx->db->config['table_prefix'] . self::TCHOICE;
    	$ttrans = $modx->db->config['table_prefix'] . self::TTRANS;
    	$tuser = $modx->db->config['table_prefix'] . self::TUSER;

    	$modx->db->query('SET AUTOCOMMIT=0;');
		$modx->db->query('START TRANSACTION;');

    	$rs1 = $modx->db->delete($tpoll, 'idPoll=' . $id);
    	$rs2 = $modx->db->delete($tchoice, 'idPoll=' . $id);
    	$rs3 = $modx->db->delete($ttrans, 'idPoll=' . $id);
    	$rs4 = $modx->db->delete($tuser, 'idPoll=' . $id);

    	$result = true;
    	if($rs1 && $rs2 && $rs3 && $rs4){
    		$modx->db->query('COMMIT;');
    	} else {
    		$modx->db->query('ROLLBACK;');
    		$result = false;
    	}
    	$modx->db->query('SET AUTOCOMMIT=1;');
    	return $result;
    }

	/** ************************************************************************
     * insert or update choices table
     * @param $id			the id to update or false for insert
     * @param $poll			the poll id (mandatory)
     * @param $title		the internal title (must not be empty)
     * @param $translation	associative array containing the language id
     * 						and the translated string for every language.
     * 						schema: langid => translation
     * @return true on success, false on failure
     */
    public function insertChoice($id, $poll, $title, array &$translation){
    	global $modx;

    	$id = intval($id);
    	$poll = intval($poll);
    	$title = trim($modx->db->escape($title));
    	if($title == '')
    		throw new EasyPollException(
    			'Invalid param in ' . __METHOD__,
    			'EP_ex_invalidparam',
    			'EP_poll_title');

    	$table = $modx->db->config['table_prefix'] . self::TCHOICE;

    	$modx->db->query('SET AUTOCOMMIT=0;');
		$modx->db->query('START TRANSACTION;');

		$errors = false;
    	if($id <= 0){
    		/*
    		$query = "INSERT INTO $table (idPoll, Title, Sorting)
    				  VALUES ($poll, '$title', (SELECT COUNT(tmp.idPoll)+1 FROM $table tmp WHERE tmp.idPoll=$poll))";
			*/
    		$rs = $modx->db->query("SELECT COUNT(tmp.idPoll)+1 AS 'count' FROM $table tmp WHERE tmp.idPoll=$poll");
    		$count = 0;
    		if($modx->db->getRecordCount($rs) > 0){
    			$row = $modx->db->getRow($rs);
    			$count = $row['count'];
    		} else {
    			$errors = true;
    		}
    		$fields = array('idPoll' => $poll, 'Title' => $title, 'Sorting' => $count);
    		$result = $id = $modx->db->insert($fields, $table);

    		//$id = $modx->db->getInsertId();
    	} else {
    		$query = "UPDATE $table SET Title='$title' WHERE idChoice = $id";
    		$result = $modx->db->query($query);
    	}

		if(!$result)
			$errors = true;

		if(!$errors){
			foreach($translation as $key => $val){
				$res = $this->insertTranslation($poll, $id, $key, $val);
				if(!$res){
					$errors = true;
					break;
				}
			}

			if($errors){
				$modx->db->query('ROLLBACK;');
			} else {
				$modx->db->query('COMMIT;');
			}
		} else {
			$modx->db->query('ROLLBACK;');
		}

		$modx->db->query('SET AUTOCOMMIT=1;');


		if($errors)
			throw new EasyPollException(
				'SQL insertion or update failed at: ' . __METHOD__,
				'EP_db_error',
				'EP_tab_polls'
			);

    	return true;
    }

    /** ************************************************************************
     * get all choices as array
     * @param $idPoll the poll id to get the choices for
     * @return array containing all values
     */
    public function getChoices($idPoll){
    	global $modx;

    	$idPoll = intval($idPoll);
    	$output = array();

    	$tblT = $modx->db->config['table_prefix'] . self::TTRANS;
    	$tblC = $modx->db->config['table_prefix'] . self::TCHOICE;
    	$tblL = $modx->db->config['table_prefix'] . self::TLANG;

    	$query = "
    	SELECT
			c.idChoice AS 'id',
			c.idPoll AS 'idpoll',
			c.Title AS 'title',
			(SELECT COUNT( ln.idLang ) FROM $tblL ln)
			- (SELECT COUNT(t.idPoll) FROM $tblT t WHERE t.idPoll = c.idPoll AND t.idChoice = c.idChoice)
			AS 'translate',
			Votes AS 'votes',
			Sorting AS 'sorting'
		FROM $tblC c WHERE idPoll=$idPoll ORDER BY c.Sorting ASC";

    	$result = $modx->db->query($query);
    	while($row = $modx->db->getRow($result)){
    		$output[] = $row;
    	}

    	return $output;
    }

    /** ************************************************************************
     * get a choice by specifying the id
     * @return array with row values
     */
	public function getChoiceById($id){
    	global $modx;

    	$table = $modx->db->config['table_prefix'] . self::TCHOICE;
    	$id = intval($id);

    	$result = $modx->db->select
    	(
    		'idChoice AS \'id\', idPoll AS \'pollid\', Title AS \'title\', Votes AS \'votes\'',
    		$table,
    		'idChoice=' . $id
    	);

    	if($modx->db->getRecordCount($result) > 0)
    		return $modx->db->getRow($result);

    	return false;
    }

    /** ************************************************************************
     * delete a choice from the db
     * @return true when successful
     */
    public function deleteChoice($id){
    	global $modx;
    	$table = $modx->db->config['table_prefix'] . self::TCHOICE;
    	$ttrans = $modx->db->config['table_prefix'] . self::TTRANS;
    	$id = intval($id);

    	$modx->db->query('SET AUTOCOMMIT=0;');
		$modx->db->query('START TRANSACTION;');
		// get the current sorting
		$rs = $modx->db->select('Sorting', $table, 'idChoice=' . $id);
		$row = $modx->db->getRow($rs);
		if($row['Sorting'])
			$modx->db->query('UPDATE ' . $table . ' SET Sorting=Sorting-1 WHERE Sorting > ' . $row['Sorting']);

    	$result = $modx->db->delete($table, 'idChoice=' . $id);
    	if($result){
    		$result2 = $modx->db->delete($ttrans, 'idChoice=' . $id);
    		if($result2){
    			$modx->db->query('COMMIT;');
    		} else {
    			$modx->db->query('ROLLBACK;');
    		}
    	} else {
    		$modx->db->query('ROLLBACK;');
    	}
    	$modx->db->query('SET AUTOCOMMIT=1;');
    	return $result == true;
    }

    /** ************************************************************************
     * change the sorting order of a choice
     * @param $id = the choice id
     * @param $up = the sorting direction. true for up, false for down
     */
    public function sortChoice($id, $up = true){
    	global $modx;

    	$id = intval($id);
    	$table = $modx->db->config['table_prefix'] . self::TCHOICE;

    	$modx->db->query('SET AUTOCOMMIT=0;');
		$modx->db->query('START TRANSACTION;');
		$rs = $modx->db->select('Sorting, idPoll', $table, 'idChoice=' . $id);
		if($modx->db->getRecordCount($rs) > 0){

			$row = $modx->db->getRow($rs);
			$sort = $row['Sorting'];
			$idPoll = $row['idPoll'];
			$result = $modx->db->select('COUNT(idPoll) AS \'count\'', $table, 'idPoll=' . $idPoll);
			$row2 = $modx->db->getRow($result);
			$max = $row2['count'];
			if($up && $sort > 1){
				$modx->db->query("UPDATE $table SET Sorting = $sort WHERE Sorting=$sort-1 AND idPoll=$idPoll");
				$modx->db->query("UPDATE $table SET Sorting = Sorting-1 WHERE idChoice=$id");
			} else if(!$up && $sort < $max){
				$modx->db->query("UPDATE $table SET Sorting = $sort WHERE Sorting=$sort+1 AND idPoll=$idPoll");
				$modx->db->query("UPDATE $table SET Sorting = Sorting+1 WHERE idChoice=$id");
			}
			$modx->db->query('COMMIT');
		}
		$modx->db->query('SET AUTOCOMMIT=1;');
    }

    /** ************************************************************************
     * delete logged ips
     * @return true upon success
     */
    public function clearIPs(){
    	global $modx;

    	$table = $modx->db->config['table_prefix'] . self::TUSER;
    	$rs = $modx->db->query("TRUNCATE TABLE $table;");
    	return $rs ? true : false;
    }

    /** ************************************************************************
     * get the translation item
     * @return the translation
     */
    public function getTranslation($idPoll, $idLang, $idChoice = 0){
    	global $modx;

    	$output = array();

    	$idPoll = intval($idPoll);
    	$idChoice = intval($idChoice);
    	$idLang = intval($idLang);

    	$table = $modx->db->config['table_prefix'] . self::TTRANS;
    	$rs = $modx->db->select('TextValue', $table, "idPoll=$idPoll AND idChoice=$idChoice AND idLang=$idLang");

    	if($modx->db->getRecordCount($rs) > 0){
    		$row = $modx->db->getRow($rs);
    		return $row['TextValue'];
    	}

    	return false;
    }

    /** ************************************************************************
     * insert or update a translation
     * @param $idPoll	the id of the poll
     * @param $idChoice	the id of the choice (might be false)
     * @param $idLang	the language id
     * @param $value	the string containing the translation of the item
     * @return true on success, false on failure
     */
    private function insertTranslation($idPoll, $idChoice, $idLang, $value){
    	global $modx;

    	$idPoll = intval($idPoll);
    	$idChoice = intval($idChoice);
    	$idLang = intval($idLang);
    	$value = trim($modx->db->escape($value));

    	if($value == '')
    		throw new EasyPollException(
    			'Invalid param in ' . __METHOD__,
    			'EP_ex_invalidparam',
    			'EP_poll_transl');

    	$table = $modx->db->config['table_prefix'] . self::TTRANS;
    	// first check if the item exists
    	$rs = $modx->db->select('*', $table, "idPoll=$idPoll AND idChoice=$idChoice AND idLang=$idLang");
    	if($modx->db->getRecordCount($rs) > 0){
    		// update
    		$result = $modx->db->update(array('TextValue' => $value), $table, "idPoll=$idPoll AND idChoice=$idChoice AND idLang=$idLang");
    	} else {
    		$fields = array('idPoll' => $idPoll, 'idLang' => $idLang, 'TextValue' => $value);
    		if($idChoice > 0)
    			$fields['idChoice'] = $idChoice;

    		$result = $modx->db->insert($fields, $table);
    	}

    	return $result == true;
    }


	/** ************************************************************************
	 * return the singleton, create it if not allready created
	 * @return EasyPollManager Object
	 */
	public static function construct(){
		if (!isset(self::$singleton)) {
			$class = __CLASS__;
			self::$singleton = new $class;
		}

		return self::$singleton;
	}
}

/** ************************************************************************
 * Own exception class for exceptions thrown by EasyPoll Manager
 */
class EasyPollException extends Exception {
	private $msgString;
	private $paramString;
	public function __construct ($message, $msgString, $paramString, $code = 0) {
		$this->msgString = $msgString;
		$this->paramString = $paramString;
		parent::__construct($message, $code);
    }

    public function getParamString(){ return $this->paramString; }
	public function getMsgString(){ return $this->msgString; }
}
?>