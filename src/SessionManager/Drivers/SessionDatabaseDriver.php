<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * CodeIgniter SessionManager Database Driver
 *
 * @package	CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author	Andrey Andreev
 * @link	https://codeigniter.com/user_guide/libraries/sessions.html
 */

namespace TinyFw\SessionManager\Drivers;

use TinyFw\SessionManager\SessionDriver;
use TinyFw\Core\DbConnection;


class SessionDatabaseDriver extends SessionDriver implements \SessionHandlerInterface {

    /**
     * DB object
     *
     * @var	object
     */
    protected $_db;

    /**
     * Row exists flag
     *
     * @var	bool
     */
    protected $_row_exists = FALSE;

    /**
     * Lock "driver" flag
     *
     * @var	string
     */
    protected $_platform = 'mysql';

    // ------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param	array	$params	Configuration parameters
     * @return	void
     */
    public function __construct(&$params)
    {
        parent::__construct($params);

//        $CI =& get_instance();
//        isset($CI->db) OR $CI->load->database();
//        $this->_db = $CI->db;
//
//        if ( ! $this->_db instanceof CI_DB_query_builder)
//        {
//            throw new Exception('Query Builder not enabled for the configured database. Aborting.');
//        }
//        elseif ($this->_db->pconnect)
//        {
//            throw new Exception('Configured database connection is persistent. Aborting.');
//        }
//        elseif ($this->_db->cache_on)
//        {
//            throw new Exception('Configured database connection has cache enabled. Aborting.');
//        }
//
//        $db_driver = $this->_db->dbdriver.(empty($this->_db->subdriver) ? '' : '_'.$this->_db->subdriver);
//        if (strpos($db_driver, 'mysql') !== FALSE)
//        {
//            $this->_platform = 'mysql';
//        }
//        elseif (in_array($db_driver, array('postgre', 'pdo_pgsql'), TRUE))
//        {
//            $this->_platform = 'postgre';
//        }
//
//        // Note: BC work-around for the old 'sess_table_name' setting, should be removed in the future.
//        if ( ! isset($this->_config['save_path']) && ($this->_config['save_path'] = config_item('sess_table_name')))
//        {
//            log_message('debug', 'SessionManager: "sess_save_path" is empty; using BC fallback to "sess_table_name".');
//        }

//        $this->_platform = $this->_config['database_platform'];

        $this->_db = DbConnection::getInstance();
    }

    // ------------------------------------------------------------------------

    /**
     * Open
     *
     * Initializes the database connection
     *
     * @param	string	$save_path	Table name
     * @param	string	$name		SessionManager cookie name, unused
     * @return	bool
     */
    public function open($save_path, $name)
    {
        return $this->_success;
    }

    // ------------------------------------------------------------------------

    /**
     * Read
     *
     * Reads session data and acquires a lock
     *
     * @param	string	$session_id	SessionManager ID
     * @return	string	Serialized session data
     */
    public function read($session_id)
    {
        if ($this->_get_lock($session_id) !== FALSE)
        {
            // Needed by write() to detect session_regenerate_id() calls
            $this->_session_id = $session_id;

            $sql = 'SELECT data FROM '.$this->_config['save_path']." WHERE id = '$session_id' ";
            if ($this->_config['match_ip'])
            {
                $sql .= " AND ip_address = '{$_SERVER['REMOTE_ADDR']}'";
            }

            $result = $this->_db->selectOneRow($sql);
            if (empty($result))
            {
                // PHP7 will reuse the same SessionHandler object after
                // ID regeneration, so we need to explicitly set this to
                // FALSE instead of relying on the default ...
                $this->_row_exists = FALSE;
                $this->_fingerprint = md5('');
                return '';
            }

            $result = $result['data'];
            $this->_fingerprint = md5($result);
            $this->_row_exists = TRUE;
            return $result;
        }

        $this->_fingerprint = md5('');
        return '';
    }

    // ------------------------------------------------------------------------

    /**
     * Write
     *
     * Writes (create / update) session data
     *
     * @param	string	$session_id	SessionManager ID
     * @param	string	$session_data	Serialized session data
     * @return	bool
     */
    public function write($session_id, $session_data)
    {
        $timestamp = time();

        // Was the ID regenerated?
        if ($session_id !== $this->_session_id)
        {
            if ( ! $this->_release_lock() OR ! $this->_get_lock($session_id))
            {
                return $this->_fail();
            }

            $this->_row_exists = FALSE;
            $this->_session_id = $session_id;
        }
        elseif ($this->_lock === FALSE)
        {
            return $this->_fail();
        }

        if ($this->_row_exists === FALSE)
        {
            $sql = "INSERT INTO {$this->_config['save_path']}(id,ip_address,timestamp,data) VALUES('{$session_id}','{$_SERVER['REMOTE_ADDR']}','$timestamp','{$session_data}')";
            if ($this->_db->insert($sql))
            {
                $this->_fingerprint = md5($session_data);
                $this->_row_exists = TRUE;
                return $this->_success;
            }

            return $this->_fail();
        }

        $sql = "UPDATE {$this->_config['save_path']} SET data = '{$session_data}', timestamp = '{$timestamp}'";
        $sql .= " WHERE id = '$session_id' ";

        if ($this->_config['match_ip'])
        {
            $sql .= " AND ip_address = '{$_SERVER['REMOTE_ADDR']}'";
        }

        if ($this->_db->update($sql))
        {
            $this->_fingerprint = md5($session_data);
            return $this->_success;
        }

        // -- Khi reload lien tuc, khi update session se bi loi, se thow Exception  --
//        return $this->_fail();
        return $this->_success;

    }

    // ------------------------------------------------------------------------

    /**
     * Close
     *
     * Releases locks
     *
     * @return	bool
     */
    public function close()
    {
        return ($this->_lock && ! $this->_release_lock())
            ? $this->_fail()
            : $this->_success;
    }

    // ------------------------------------------------------------------------

    /**
     * Destroy
     *
     * Destroys the current session.
     *
     * @param	string	$session_id	SessionManager ID
     * @return	bool
     */
    public function destroy($session_id)
    {
        if ($this->_lock)
        {
            $sql = "DELETE FROM {$this->_config['save_path']} ";
            $sql .= " WHERE id = '$session_id' ";
            if ($this->_config['match_ip'])
            {
                $sql .= " AND ip_address = '{$_SERVER['REMOTE_ADDR']}'";
            }

            if ( ! $this->_db->delete($sql))
            {
                return $this->_fail();
            }
        }

        if ($this->close() === $this->_success)
        {
            $this->_cookie_destroy();
            return $this->_success;
        }

        return $this->_fail();
    }

    // ------------------------------------------------------------------------

    /**
     * Garbage Collector
     *
     * Deletes expired sessions
     *
     * @param	int 	$maxlifetime	Maximum lifetime of sessions
     * @return	bool
     */
    public function gc($maxlifetime)
    {
        return ($this->_db->delete("DELETE FROM {$this->_config['save_path']} WHERE 'timestamp' < ".(time() - $maxlifetime)))
            ? $this->_success
            : $this->_fail();
    }

    // ------------------------------------------------------------------------

    /**
     * Get lock
     *
     * Acquires a lock, depending on the underlying platform.
     *
     * @param	string	$session_id	SessionManager ID
     * @return	bool
     */
    protected function _get_lock($session_id)
    {
        if ($this->_platform === 'mysql')
        {
            $arg = $session_id.($this->_config['match_ip'] ? '_'.$_SERVER['REMOTE_ADDR'] : '');

//            if ($this->_db->query("SELECT GET_LOCK('".$arg."', 300) AS ci_session_lock")->row()->ci_session_lock)
//            if($this->_db->selectOneRow("SELECT GET_LOCK('".$arg."', 300) AS ci_session_lock"))
            $row = $this->_db->selectOneRow("SELECT GET_LOCK('".$arg."', 300) AS ci_session_lock");
            if($row['ci_session_lock'])
            {
//                die($row['ci_session_lock']);
                $this->_lock = $arg;
                return TRUE;
            }

            return FALSE;
        }

        return parent::_get_lock($session_id);
    }

    // ------------------------------------------------------------------------

    /**
     * Release lock
     *
     * Releases a previously acquired lock
     *
     * @return	bool
     */
    protected function _release_lock()
    {
        if ( ! $this->_lock)
        {
            return TRUE;
        }

        if ($this->_platform === 'mysql')
        {
//            if ($this->_db->query("SELECT RELEASE_LOCK('".$this->_lock."') AS ci_session_lock")->row()->ci_session_lock)
            $row = $this->_db->selectOneRow("SELECT RELEASE_LOCK('".$this->_lock."') AS ci_session_lock");
            if ($row['ci_session_lock'])
            {
                $this->_lock = FALSE;
                return TRUE;
            }

            return FALSE;
        }

        return parent::_release_lock();
    }
}