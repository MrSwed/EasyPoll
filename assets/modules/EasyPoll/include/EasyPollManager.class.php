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
 * - MODx >=1.0.10, others to be tested
 * - PHP Version 5 or greater
 * - MySQL Version 4.1 or better
 *
 * @author banal, vanchelo <brezhnev.ivan@yahoo.com>
 * @version 0.3.4 <2014-09-23>
 */
class EasyPollManager
{
    const TPOLL = 'ep_poll';
    const TCHOICE = 'ep_choice';
    const TLANG = 'ep_language';
    const TUSER = 'ep_userip';
    const TTRANS = 'ep_translation';

    /**
     * @var EasyPollManager The Singleton Object
     */
    private static $singleton;
    /**
     * @var DocumentParser
     */
    protected $modx;

    /**
     * Private, constructor. Prevents external construction of Objects
     */
    private function __construct() {}

    /**
     * Override the clone Method and throw Exception
     * if somebody tries to clone the singleton
     */
    public function __clone()
    {
        throw new Exception('Cloning not allowed on singletons');
    }

    /**
     * Insert or update language table
     *
     * @param int $id the id to update or false for insert
     * @param string $short short language string (2-3 letters)
     * @param string $long long version of language name
     *
     * @return bool True on success, False on failure
     *
     * @throws EasyPollException
     */
    public function insertLanguage($id, $short, $long)
    {
        $short = strtolower($short);
        if (!preg_match('/^[a-z]{1,3}$/', $short)) {
            throw new EasyPollException(
                'Invalid param in ' . __METHOD__,
                'EP_ex_invalidparam',
                'EP_lang_short'
            );
        }

        $long = trim($this->modx->db->escape($long));
        $table = $this->modx->db->config['table_prefix'] . self::TLANG;

        $id = intval($id);
        $fields = array('LangShort' => $short, 'LangName' => $long);
        if ($id <= 0) {
            $result = $this->modx->db->insert($fields, $table);
        } else {
            $result = $this->modx->db->update($fields, $table, 'idLang=' . $id);
        }

        return $result == true;
    }

    /**
     * Get all languages as array
     *
     * @return array containing all values
     */
    public function getLanguages()
    {
        $output = array();
        $table = $this->modx->db->config['table_prefix'] . self::TLANG;

        $result = $this->modx->db->select(
            "idLang AS 'id', LangShort AS 'short', LangName AS 'long'",
            $table,
            '',
            'LangShort ASC'
        );

        while ($row = $this->modx->db->getRow($result)) {
            $output[] = $row;
        }

        return $output;
    }

    /**
     * Get a language by specifying the id
     *
     * @param int $id Language ID
     *
     * @return array with id, short and long name
     */
    public function getLangById($id)
    {
        $id = (int) $id;

        $table = $this->modx->db->config['table_prefix'] . self::TLANG;

        $result = $this->modx->db->select(
            "idLang AS 'id', LangShort AS 'short', LangName AS 'long'",
            $table,
            "idLang = {$id}"
        );

        if ($this->modx->db->getRecordCount($result) > 0) {
            return $this->modx->db->getRow($result);
        }

        return false;
    }

    /**
     * Delete a language from the db
     *
     * @param int $id
     *
     * @return bool True when successful
     */
    public function deleteLanguage($id)
    {
        $id = (int) $id;

        $tlang = $this->modx->db->config['table_prefix'] . self::TLANG;
        $ttrans = $this->modx->db->config['table_prefix'] . self::TTRANS;

        $this->modx->db->query('SET AUTOCOMMIT=0;');
        $this->modx->db->query('START TRANSACTION;');

        $rs1 = $this->modx->db->delete($tlang, 'idLang=' . $id);
        $rs2 = $this->modx->db->delete($ttrans, 'idLang=' . $id);

        $result = true;
        if ($rs1 && $rs2) {
            $this->modx->db->query('COMMIT;');
        } else {
            $this->modx->db->query('ROLLBACK;');
            $result = false;
        }
        $this->modx->db->query('SET AUTOCOMMIT=1;');

        return $result;
    }

    /**
     * Insert or update a poll
     *
     * @param int $id the id to update or false for insert
     * @param string $title the polls internal title. must not be empty
     * @param array $translation associative array containing the language id
     *                        and the translated string for every language.
     *                        schema: langid => translation
     * @param bool $isActive set the poll to be active
     * @param string|bool $startDate starting date for the poll or false
     * @param string|bool $endDate ending date for the poll or false
     *
     * @return bool True on success, false on failure
     *
     * @throws EasyPollException
     */
    public function insertPoll($id, $title, array & $translation, $isActive = false, $startDate = false, $endDate = false)
    {
        $id = (int) $id;
        $title = trim($this->modx->db->escape($title));
        $table = $this->modx->getFullTableName(self::TPOLL);

        if ($title == '') {
            throw new EasyPollException(
                'Invalid param in ' . __METHOD__,
                'EP_ex_invalidparam',
                'EP_poll_title'
            );
        }

        $isActive = $isActive == true ? 1 : 0;
        $stDate = $startDate == false ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($startDate));
        $enDate = $endDate == false ? 0 : date('Y-m-d H:i:s', strtotime($endDate));

        $this->modx->db->query('SET AUTOCOMMIT=0;');
        $this->modx->db->query('START TRANSACTION;');

        $fields = array(
            'Title' => $title,
            'isActive' => $isActive,
            'StartDate' => $stDate,
            'EndDate' => $enDate
        );
        $errors = false;
        if ($id > 0) {
            $result = $this->modx->db->update($fields, $table, "idPoll = {$id}");
        } else {
            $result = $this->modx->db->insert($fields, $table);
            $id = $result;
        }

        if (!$result) {
            $errors = true;
        }

        if (!$errors) {
            foreach ($translation as $key => $val) {
                $res = $this->insertTranslation($id, false, $key, $val);
                if (!$res) {
                    $errors = true;
                    break;
                }
            }

            if ($errors) {
                $this->modx->db->query('ROLLBACK;');
            } else {
                $this->modx->db->query('COMMIT;');
            }
        } else {
            $this->modx->db->query('ROLLBACK;');
        }

        $this->modx->db->query('SET AUTOCOMMIT=1;');

        if ($errors) {
            throw new EasyPollException(
                'SQL insertion or update failed at: ' . __METHOD__,
                'EP_db_error',
                'EP_tab_polls'
            );
        }

        return true;
    }

    /**
     * Get all polls as array
     *
     * @param string $dateformat = format string for date
     *
     * @return array containing all values
     *            id = the poll id
     *            title = the poll internal title
     *            sdate = the startdate or NULL
     *            edate = the enddate or NULL
     *            active = either 1 or 0 if the poll is set active or inactive
     *            translate = the number of items that still need to be translated
     *                        for this poll entry. 0 = all is translated
     *            votes = number of votes for this poll
     *
     * @throws EasyPollException
     */
    public function getPolls($dateformat = '%Y-%m-%d')
    {
        $output = array();

        if (preg_match("/[\n'\"]/", $dateformat)) {
            throw new EasyPollException(
                'Invalid characters in date format: ' . __METHOD__,
                'EP_ex_undef',
                'EP_tab_polls'
            );
        }

        $tblP = $this->modx->getFullTableName(self::TPOLL);
        $tblT = $this->modx->getFullTableName(self::TTRANS);
        $tblC = $this->modx->getFullTableName(self::TCHOICE);
        $tblL = $this->modx->getFullTableName(self::TLANG);

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

        $result = $this->modx->db->query($query);
        while ($row = $this->modx->db->getRow($result)) {
            $output[] = $row;
        }

        return $output;
    }

    /**
     * Get a poll by specifying the id
     *
     * @param int $id The poll id
     * @param string $dateformat The format for the date string
     *
     * @return array with id, title, sdate, edate and active or false upon failure
     *
     * @throws EasyPollException
     */
    public function getPollById($id, $dateformat = '%Y-%m-%d')
    {
        $id = (int) $id;

        if (preg_match("/[\n'\"]/", $dateformat)) {
            throw new EasyPollException(
                'Invalid characters in date format: ' . __METHOD__,
                'EP_ex_undef',
                'EP_tab_polls'
            );
        }

        $table = $this->modx->getFullTableName(self::TPOLL);

        $result = $this->modx->db->select(
            "idPoll AS 'id', Title AS 'title', IF(StartDate > 0, DATE_FORMAT(StartDate, '{$dateformat}'), '') AS 'sdate',
            IF(EndDate > 0, DATE_FORMAT(EndDate, '{$dateformat}'), '') AS 'edate', isActive AS 'active'",
            $table,
            "idPoll='{$id}'"
        );

        if ($this->modx->db->getRecordCount($result) > 0) {
            return $this->modx->db->getRow($result);
        }

        return false;
    }

    /**
     * Delete a poll from the db
     *
     * @param int $id
     *
     * @return true when successful
     */
    public function deletePoll($id)
    {
        $id = (int) $id;

        $tpoll = $this->modx->getFullTableName(self::TPOLL);
        $tchoice = $this->modx->getFullTableName(self::TCHOICE);
        $ttrans = $this->modx->getFullTableName(self::TTRANS);
        $tuser = $this->modx->getFullTableName(self::TUSER);

        $this->modx->db->query('SET AUTOCOMMIT=0;');
        $this->modx->db->query('START TRANSACTION;');

        $rs1 = $this->modx->db->delete($tpoll, "idPoll = {$id}");
        $rs2 = $this->modx->db->delete($tchoice, "idPoll = {$id}");
        $rs3 = $this->modx->db->delete($ttrans, "idPoll = {$id}");
        $rs4 = $this->modx->db->delete($tuser, "idPoll = {$id}");

        $result = true;
        if ($rs1 && $rs2 && $rs3 && $rs4) {
            $this->modx->db->query('COMMIT;');
        } else {
            $this->modx->db->query('ROLLBACK;');
            $result = false;
        }
        $this->modx->db->query('SET AUTOCOMMIT=1;');

        return $result;
    }

    /**
     * Insert or update choices table
     *
     * @param int $id The id to update or false for insert
     * @param int $poll The poll id (mandatory)
     * @param string $title The internal title (must not be empty)
     * @param array $translation Associative array containing the language id
     *                        and the translated string for every language.
     *                        schema: langid => translation
     * @return bool True on success, False on failure
     *
     * @throws EasyPollException
     */
    public function insertChoice($id, $poll, $title, array & $translation)
    {
        $id = (int) $id;
        $poll = (int) $poll;
        $title = trim($this->modx->db->escape($title));

        if ($title == '') {
            throw new EasyPollException(
                'Invalid param in ' . __METHOD__,
                'EP_ex_invalidparam',
                'EP_poll_title'
            );
        }

        $table = $this->modx->getFullTableName(self::TCHOICE);

        $this->modx->db->query('SET AUTOCOMMIT=0;');
        $this->modx->db->query('START TRANSACTION;');

        $errors = false;
        if ($id <= 0) {
            /*
            $query = "INSERT INTO $table (idPoll, Title, Sorting)
                      VALUES ($poll, '$title', (SELECT COUNT(tmp.idPoll)+1 FROM $table tmp WHERE tmp.idPoll=$poll))";
            */
            $rs = $this->modx->db->query("SELECT COUNT(tmp.idPoll)+1 AS 'count' FROM $table tmp WHERE tmp.idPoll=$poll");
            $count = 0;
            if ($this->modx->db->getRecordCount($rs) > 0) {
                $row = $this->modx->db->getRow($rs);
                $count = $row['count'];
            } else {
                $errors = true;
            }
            $fields = array('idPoll' => $poll, 'Title' => $title, 'Sorting' => $count);
            $result = $id = $this->modx->db->insert($fields, $table);

            //$id = $this->modx->db->getInsertId();
        } else {
            $query = "UPDATE {$table} SET Title='{$title}' WHERE idChoice = {$id}";
            $result = $this->modx->db->query($query);
        }

        if (!$result)
            $errors = true;

        if (!$errors) {
            foreach ($translation as $key => $val) {
                $res = $this->insertTranslation($poll, $id, $key, $val);
                if (!$res) {
                    $errors = true;
                    break;
                }
            }

            if ($errors) {
                $this->modx->db->query('ROLLBACK;');
            } else {
                $this->modx->db->query('COMMIT;');
            }
        } else {
            $this->modx->db->query('ROLLBACK;');
        }

        $this->modx->db->query('SET AUTOCOMMIT=1;');

        if ($errors) {
            throw new EasyPollException(
                'SQL insertion or update failed at: ' . __METHOD__,
                'EP_db_error',
                'EP_tab_polls'
            );
        }

        return true;
    }

    /**
     * Get all choices as array
     *
     * @param int $idPoll The poll id to get the choices for
     *
     * @return array containing all values
     */
    public function getChoices($idPoll)
    {
        $idPoll = (int) $idPoll;
        $output = array();

        $tblT = $this->modx->getFullTableName(self::TTRANS);
        $tblC = $this->modx->getFullTableName(self::TCHOICE);
        $tblL = $this->modx->getFullTableName(self::TLANG);

        $query = "
    	SELECT
			c.idChoice AS 'id',
			c.idPoll AS 'idpoll',
			c.Title AS 'title',
			(SELECT COUNT( ln.idLang ) FROM {$tblL} ln)
			- (SELECT COUNT(t.idPoll) FROM {$tblT} t WHERE t.idPoll = c.idPoll AND t.idChoice = c.idChoice)
			AS 'translate',
			Votes AS 'votes',
			Sorting AS 'sorting'
		FROM {$tblC} c WHERE idPoll = {$idPoll} ORDER BY c.Sorting ASC";

        $result = $this->modx->db->query($query);
        while ($row = $this->modx->db->getRow($result)) {
            $output[] = $row;
        }

        return $output;
    }

    /**
     * Get a choice by specifying the id
     *
     * @param int $id
     *
     * @return array with row values
     */
    public function getChoiceById($id)
    {
        $id = (int) $id;
        $table = $this->modx->getFullTableName(self::TCHOICE);

        $result = $this->modx->db->select(
            "idChoice AS 'id', idPoll AS 'pollid', Title AS 'title', Votes AS 'votes'",
            $table,
            "idChoice = {$id}"
        );

        if ($row = $this->modx->db->getRow($result)) {
            return $row;
        }

        return false;
    }

    /**
     * delete a choice from the db
     *
     * @param int $id
     * @return bool
     */
    public function deleteChoice($id)
    {
        $id = (int) $id;
        $table = $this->modx->getFullTableName(self::TCHOICE);
        $ttrans = $this->modx->getFullTableName(self::TTRANS);

        $this->modx->db->query('SET AUTOCOMMIT=0;');
        $this->modx->db->query('START TRANSACTION;');
        // get the current sorting
        $rs = $this->modx->db->select('Sorting', $table, "idChoice = {$id}");
        $row = $this->modx->db->getRow($rs);
        if ($row['Sorting']) {
            $this->modx->db->query("UPDATE {$table} SET Sorting=Sorting-1 WHERE Sorting > {$row['Sorting']}");
        }

        $result = $this->modx->db->delete($table, "idChoice = {$id}");
        if ($result) {
            $result2 = $this->modx->db->delete($ttrans, "idChoice = {$id}");
            if ($result2) {
                $this->modx->db->query('COMMIT;');
            } else {
                $this->modx->db->query('ROLLBACK;');
            }
        } else {
            $this->modx->db->query('ROLLBACK;');
        }
        $this->modx->db->query('SET AUTOCOMMIT=1;');

        return $result == true;
    }

    /**
     * Change the sorting order of a choice
     *
     * @param int $id = The choice id
     * @param bool $up = The sorting direction. true for up, false for down
     */
    public function sortChoice($id, $up = true)
    {
        $id = (int) $id;
        $table = $this->modx->getFullTableName(self::TCHOICE);

        $this->modx->db->query('SET AUTOCOMMIT=0;');
        $this->modx->db->query('START TRANSACTION;');
        $rs = $this->modx->db->select('Sorting, idPoll', $table, "idChoice = {$id}");
        if ($this->modx->db->getRecordCount($rs) > 0) {
            $row = $this->modx->db->getRow($rs);
            $sort = $row['Sorting'];
            $idPoll = $row['idPoll'];
            $result = $this->modx->db->select('COUNT(idPoll) AS \'count\'', $table, "idPoll = {$idPoll}");
            $row2 = $this->modx->db->getRow($result);
            $max = $row2['count'];
            if ($up && $sort > 1) {
                $this->modx->db->query("UPDATE {$table} SET Sorting = {$sort} WHERE Sorting = {$sort}-1 AND idPoll = {$idPoll}");
                $this->modx->db->query("UPDATE {$table} SET Sorting = Sorting-1 WHERE idChoice=$id");
            } else if (!$up && $sort < $max) {
                $this->modx->db->query("UPDATE {$table} SET Sorting = {$sort} WHERE Sorting = {$sort}+1 AND idPoll = {$idPoll}");
                $this->modx->db->query("UPDATE {$table} SET Sorting = Sorting+1 WHERE idChoice = {$id}");
            }
            $this->modx->db->query('COMMIT');
        }
        $this->modx->db->query('SET AUTOCOMMIT=1;');
    }

    /**
     * Delete logged ips
     *
     * @return bool True upon success
     */
    public function clearIPs()
    {
        $table = $this->modx->db->config['table_prefix'] . self::TUSER;
        $rs = $this->modx->db->query("TRUNCATE TABLE {$table};");
        return $rs ? true : false;
    }

    /**
     * Get the translation item
     *
     * @param $idPoll
     * @param $idLang
     * @param int $idChoice
     *
     * @return bool
     */
    public function getTranslation($idPoll, $idLang, $idChoice = 0)
    {
        $idPoll = (int) $idPoll;
        $idChoice = (int) $idChoice;
        $idLang = (int) $idLang;

        $table = $this->modx->getFullTableName(self::TTRANS);
        $rs = $this->modx->db->select('TextValue', $table, "idPoll=$idPoll AND idChoice=$idChoice AND idLang=$idLang");

        if ($this->modx->db->getRecordCount($rs) > 0) {
            $row = $this->modx->db->getRow($rs);
            return $row['TextValue'];
        }

        return false;
    }

    /**
     * Insert or update a translation
     *
     * @param int $idPoll The id of the poll
     * @param int $idChoice The id of the choice (might be false)
     * @param int $idLang The language id
     * @param string $value The string containing the translation of the item
     *
     * @return bool True on success, False on failure
     *
     * @throws EasyPollException
     */
    private function insertTranslation($idPoll, $idChoice, $idLang, $value)
    {
        $idPoll = (int) $idPoll;
        $idChoice = (int) $idChoice;
        $idLang = (int) $idLang;
        $value = trim($this->modx->db->escape($value));

        if ($value == '') {
            throw new EasyPollException(
                'Invalid param in ' . __METHOD__,
                'EP_ex_invalidparam',
                'EP_poll_transl'
            );
        }

        $table = $this->modx->getFullTableName(self::TTRANS);
        // first check if the item exists
        $rs = $this->modx->db->select(
            '*',
            $table,
            "idPoll = {$idPoll} AND idChoice = {$idChoice} AND idLang = {$idLang}"
        );

        if ($this->modx->db->getRecordCount($rs) > 0) {
            // update
            $result = $this->modx->db->update(
                array('TextValue' => $value),
                $table,
                "idPoll = {$idPoll} AND idChoice = {$idChoice} AND idLang = {$idLang}"
            );
        } else {
            $result = $this->modx->db->insert(array(
                'idPoll' => $idPoll,
                'idChoice' => $idChoice,
                'idLang' => $idLang,
                'TextValue' => $value,
            ), $table);
        }

        return $result === 0 || $result == true;
    }

    /**
     * @param DocumentParser $modx
     */
    public function setModx(DocumentParser & $modx)
    {
        $this->modx =& $modx;
    }

    /**
     * Return the singleton, create it if not allready created
     *
     * @param DocumentParser $modx
     *
     * @return EasyPollManager Object
     */
    public static function instance(DocumentParser & $modx = null)
    {
        if (!isset(self::$singleton)) {
            $class = __CLASS__;
            self::$singleton = new $class;
            self::$singleton->setModx($modx);
        }

        return self::$singleton;
    }
}

/**
 * Own exception class for exceptions thrown by EasyPoll Manager
 */
class EasyPollException extends Exception
{
    private $msgString;
    private $paramString;

    public function __construct($message, $msgString, $paramString, $code = 0)
    {
        $this->msgString = $msgString;
        $this->paramString = $paramString;
        parent::__construct($message, $code);
    }

    public function getParamString()
    {
        return $this->paramString;
    }

    public function getMsgString()
    {
        return $this->msgString;
    }
}
