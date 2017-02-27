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
 * @since	Version 2.0.0
 * @filesource
 */

/**
 * CodeIgniter SessionManager Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author		Andrey Andreev
 * @link		https://codeigniter.com/user_guide/libraries/sessions.html
 */
namespace TinyFw\SessionManager;

class Session {

    /**
     * Userdata array
     *
     * Just a reference to $_SESSION, for BC purposes.
     */
    public $userdata;

    protected $_driver = 'files';
    protected $_config;
    protected $_sid_regexp;

    // ------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param	array	$params	Configuration parameters
     * @return	void
     */
    public function __construct(array $params = array())
    {
        if ((bool) ini_get('session.auto_start'))
        {
            throw new Exception("SessionManager: session.auto_start is enabled in php.ini. Aborting.");
            return;
        }
        elseif (!empty($params['driver']))
        {
            $this->_driver = $params['driver'];
            unset($params['driver']);
        }

        $class = '\TinyFw\SessionManager\Drivers\Session'.ucfirst($this->_driver).'Driver';

        // Configuration ...
        $this->_configure($params);
        $this->_config['_sid_regexp'] = $this->_sid_regexp;

        $class = new $class($this->_config);

        if ($class instanceof SessionHandlerInterface)
        {
            session_set_save_handler($class, TRUE);
        }
        else
        {
            throw new Exception("SessionManager: Driver '".$this->_driver."' doesn't implement SessionHandlerInterface. Aborting.");
            return;
        }

        // Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
        if (isset($_COOKIE[$this->_config['cookie_name']])
            && (
                ! is_string($_COOKIE[$this->_config['cookie_name']])
                OR ! preg_match('#\A'.$this->_sid_regexp.'\z#', $_COOKIE[$this->_config['cookie_name']])
            )
        )
        {
            unset($_COOKIE[$this->_config['cookie_name']]);
        }

        // -- Set session name for share session --
        session_name($params['session_name']);
        // -- Start session --
        session_start();

        // Is session ID auto-regeneration configured? (ignoring ajax requests)
        if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
            && ($regenerate_time = $params['time_to_update']) > 0
        )
        {
            if ( ! isset($_SESSION['__ci_last_regenerate']))
            {
                $_SESSION['__ci_last_regenerate'] = time();
            }
            elseif ($_SESSION['__ci_last_regenerate'] < (time() - $regenerate_time))
            {
                $this->sess_regenerate((bool) $params['regenerate_destroy']);
            }
        }
        // Another work-around ... PHP doesn't seem to send the session cookie
        // unless it is being currently created or regenerated
        elseif (isset($_COOKIE[$this->_config['cookie_name']]) && $_COOKIE[$this->_config['cookie_name']] === session_id())
        {
            setcookie(
                $this->_config['cookie_name'],
                session_id(),
                (empty($this->_config['cookie_lifetime']) ? 0 : time() + $this->_config['cookie_lifetime']),
                $this->_config['cookie_path'],
                $this->_config['cookie_domain'],
                $this->_config['cookie_secure'],
                TRUE
            );
        }

        $this->_ci_init_vars();
    }

    // ------------------------------------------------------------------------
    /**
     * Configuration
     *
     * Handle input parameters and configuration defaults
     *
     * @param	array	&$params	Input parameters
     * @return	void
     */
    protected function _configure(&$params)
    {
        $expiration = $params['expiration'];

        if (isset($params['cookie_lifetime']))
        {
            $params['cookie_lifetime'] = (int) $params['cookie_lifetime'];
        }
        else
        {
            $params['cookie_lifetime'] = ( ! isset($expiration)) ? 0 : (int) $expiration;
        }

        if (empty($params['cookie_name']))
        {
            $params['cookie_name'] = ini_get('session.name');
        }
        else
        {
            ini_set('session.name', $params['cookie_name']);
        }

        session_set_cookie_params(
            $params['cookie_lifetime'],
            $params['cookie_path'],
            $params['cookie_domain'],
            $params['cookie_secure'],
            TRUE // HttpOnly; Yes, this is intentional and not configurable for security reasons
        );

        if (empty($expiration))
        {
            $params['expiration'] = (int) ini_get('session.gc_maxlifetime');
        }
        else
        {
            $params['expiration'] = (int) $expiration;
            ini_set('session.gc_maxlifetime', $expiration);
        }

        $params['match_ip'] = (bool) $params['match_ip'];

        $this->_config = $params;

        // Security is king
        ini_set('session.use_trans_sid', 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 4);

        $this->_configure_sid_length();
    }

    // ------------------------------------------------------------------------
    /**
     * Configure session ID length
     *
     * To make life easier, we used to force SHA-1 and 4 bits per
     * character on everyone. And of course, someone was unhappy.
     *
     * Then PHP 7.1 broke backwards-compatibility because ext/session
     * is such a mess that nobody wants to touch it with a pole stick,
     * and the one guy who does, nobody has the energy to argue with.
     *
     * So we were forced to make changes, and OF COURSE something was
     * going to break and now we have this pile of shit. -- Narf
     *
     * @return	void
     */
    protected function _configure_sid_length()
    {
        if (PHP_VERSION_ID < 70100)
        {
            $hash_function = ini_get('session.hash_function');
            if (ctype_digit($hash_function))
            {
                if ($hash_function !== '1')
                {
                    ini_set('session.hash_function', 1);
                }
                $bits = 160;
            }
            elseif ( ! in_array($hash_function, hash_algos(), TRUE))
            {
                ini_set('session.hash_function', 1);
                $bits = 160;
            }
            elseif (($bits = strlen(hash($hash_function, 'dummy', false)) * 4) < 160)
            {
                ini_set('session.hash_function', 1);
                $bits = 160;
            }
            $bits_per_character = (int) ini_get('session.hash_bits_per_character');
            $sid_length         = (int) ceil($bits / $bits_per_character);
        }
        else
        {
            $bits_per_character = (int) ini_get('session.sid_bits_per_character');
            $sid_length         = (int) ini_get('session.sid_length');
            if (($bits = $sid_length * $bits_per_character) < 160)
            {
                // Add as many more characters as necessary to reach at least 160 bits
                $sid_length += (int) ceil((160 % $bits) / $bits_per_character);
                ini_set('session.sid_length', $sid_length);
            }
        }
        // Yes, 4,5,6 are the only known possible values as of 2016-10-27
        switch ($bits_per_character)
        {
            case 4:
                $this->_sid_regexp = '[0-9a-f]';
                break;
            case 5:
                $this->_sid_regexp = '[0-9a-v]';
                break;
            case 6:
                $this->_sid_regexp = '[0-9a-zA-Z,-]';
                break;
        }
        $this->_sid_regexp .= '{'.$sid_length.'}';
    }

    // ------------------------------------------------------------------------

    /**
     * Handle temporary variables
     *
     * Clears old "flash" data, marks the new one for deletion and handles
     * "temp" data deletion.
     *
     * @return	void
     */
    protected function _ci_init_vars()
    {
        if ( ! empty($_SESSION['__ci_vars']))
        {
            $current_time = time();

            foreach ($_SESSION['__ci_vars'] as $key => &$value)
            {
                if ($value === 'new')
                {
                    $_SESSION['__ci_vars'][$key] = 'old';
                }
                // Hacky, but 'old' will (implicitly) always be less than time() ;)
                // DO NOT move this above the 'new' check!
                elseif ($value < $current_time)
                {
                    unset($_SESSION[$key], $_SESSION['__ci_vars'][$key]);
                }
            }

            if (empty($_SESSION['__ci_vars']))
            {
                unset($_SESSION['__ci_vars']);
            }
        }

        $this->userdata =& $_SESSION;
    }

    // ------------------------------------------------------------------------

    /**
     * get()
     *
     * @param	string	$key	'session_id' or a session data key
     * @return	mixed
     */
    public function get($key)
    {
        // Note: Keep this order the same, just in case somebody wants to
        //       use 'session_id' as a session data key, for whatever reason
        if (isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }
        elseif ($key === 'session_id')
        {
            return session_id();
        }

        return NULL;
    }

    // ------------------------------------------------------------------------

    /**
     * has()
     *
     * @param	string	$key	'session_id' or a session data key
     * @return	bool
     */
    public function has($key)
    {
        if ($key === 'session_id')
        {
            return (session_status() === PHP_SESSION_ACTIVE);
        }

        return isset($_SESSION[$key]);
    }

    // ------------------------------------------------------------------------

    /**
     * set()
     *
     * @param	string	$key	SessionManager data key
     * @param	mixed	$value	SessionManager data value
     * @return	void
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    // ------------------------------------------------------------------------

    /**
     * SessionManager destroy
     *
     * Legacy CI_Session compatibility method
     *
     * @return	void
     */
    public function sess_destroy()
    {
        session_destroy();
    }

    // ------------------------------------------------------------------------

    /**
     * SessionManager regenerate
     *
     * Legacy CI_Session compatibility method
     *
     * @param	bool	$destroy	Destroy old session data flag
     * @return	void
     */
    public function sess_regenerate($destroy = FALSE)
    {
        $_SESSION['__ci_last_regenerate'] = time();
        session_regenerate_id($destroy);
    }

    // ------------------------------------------------------------------------

    /**
     * Get userdata reference
     *
     * Legacy CI_Session compatibility method
     *
     * @returns	array
     */
    public function &get_userdata()
    {
        return $_SESSION;
    }

    // ------------------------------------------------------------------------

    /**
     * Userdata (fetch)
     *
     * Legacy CI_Session compatibility method
     *
     * @param	string	$key	SessionManager data key
     * @return	mixed	SessionManager data value or NULL if not found
     */
    public function userdata($key = NULL)
    {
        if (isset($key))
        {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
        }
        elseif (empty($_SESSION))
        {
            return array();
        }

        $userdata = array();
        $_exclude = array_merge(
            array('__ci_vars'),
            $this->get_flash_keys()
//            , $this->get_temp_keys()
        );

        foreach (array_keys($_SESSION) as $key)
        {
            if ( ! in_array($key, $_exclude, TRUE))
            {
                $userdata[$key] = $_SESSION[$key];
            }
        }

        return $userdata;
    }

    // ------------------------------------------------------------------------

    /**
     * Set userdata
     *
     * Legacy CI_Session compatibility method
     *
     * @param	mixed	$data	SessionManager data key or an associative array
     * @param	mixed	$value	Value to store
     * @return	void
     */
    public function set_userdata($data, $value = NULL)
    {
        if (is_array($data))
        {
            foreach ($data as $key => &$value)
            {
                $_SESSION[$key] = $value;
            }

            return;
        }

        $_SESSION[$data] = $value;
    }

    // ------------------------------------------------------------------------

    /**
     * Unset userdata
     *
     * Legacy CI_Session compatibility method
     *
     * @param	mixed	$key	SessionManager data key(s)
     * @return	void
     */
    public function unset_userdata($key)
    {
        if (is_array($key))
        {
            foreach ($key as $k)
            {
                unset($_SESSION[$k]);
            }

            return;
        }

        unset($_SESSION[$key]);
    }

    // ------------------------------------------------------------------------

    /**
     * All userdata (fetch)
     *
     * Legacy CI_Session compatibility method
     *
     * @return	array	$_SESSION, excluding flash data items
     */
    public function all_userdata()
    {
        return $this->userdata();
    }

    // ------------------------------------------------------------------------

    /**
     * Has userdata
     *
     * Legacy CI_Session compatibility method
     *
     * @param	string	$key	SessionManager data key
     * @return	bool
     */
    public function has_userdata($key)
    {
        return isset($_SESSION[$key]);
    }

    // ------------------------------------------------------------------------

    /**
     * Mark as flash
     *
     * @param	mixed	$key	SessionManager data key(s)
     * @return	bool
     */
    public function mark_as_flash($key)
    {
        if (is_array($key))
        {
            for ($i = 0, $c = count($key); $i < $c; $i++)
            {
                if ( ! isset($_SESSION[$key[$i]]))
                {
                    return FALSE;
                }
            }

            $new = array_fill_keys($key, 'new');

            $_SESSION['__ci_vars'] = isset($_SESSION['__ci_vars'])
                ? array_merge($_SESSION['__ci_vars'], $new)
                : $new;

            return TRUE;
        }

        if ( ! isset($_SESSION[$key]))
        {
            return FALSE;
        }

        $_SESSION['__ci_vars'][$key] = 'new';
        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get flash keys
     *
     * @return	array
     */
    public function get_flash_keys()
    {
        if ( ! isset($_SESSION['__ci_vars']))
        {
            return array();
        }

        $keys = array();
        foreach (array_keys($_SESSION['__ci_vars']) as $key)
        {
            is_int($_SESSION['__ci_vars'][$key]) OR $keys[] = $key;
        }

        return $keys;
    }

    // ------------------------------------------------------------------------

    /**
     * Unmark flash
     *
     * @param	mixed	$key	SessionManager data key(s)
     * @return	void
     */
    public function unmark_flash($key)
    {
        if (empty($_SESSION['__ci_vars']))
        {
            return;
        }

        is_array($key) OR $key = array($key);

        foreach ($key as $k)
        {
            if (isset($_SESSION['__ci_vars'][$k]) && ! is_int($_SESSION['__ci_vars'][$k]))
            {
                unset($_SESSION['__ci_vars'][$k]);
            }
        }

        if (empty($_SESSION['__ci_vars']))
        {
            unset($_SESSION['__ci_vars']);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Flashdata (fetch)
     *
     * Legacy CI_Session compatibility method
     *
     * @param	string	$key	SessionManager data key
     * @return	mixed	SessionManager data value or NULL if not found
     */
    public function getFlashData($key = NULL)
    {
        if (isset($key))
        {
            return (isset($_SESSION['__ci_vars'], $_SESSION['__ci_vars'][$key], $_SESSION[$key]) && ! is_int($_SESSION['__ci_vars'][$key]))
                ? $_SESSION[$key]
                : NULL;
        }

        $flashdata = array();

        if ( ! empty($_SESSION['__ci_vars']))
        {
            foreach ($_SESSION['__ci_vars'] as $key => &$value)
            {
                is_int($value) OR $flashdata[$key] = $_SESSION[$key];
            }
        }

        return $flashdata;
    }

    // ------------------------------------------------------------------------

    /**
     * Set flashdata
     *
     * Legacy CI_Session compatibility method
     *
     * @param	mixed	$data	SessionManager data key or an associative array
     * @param	mixed	$value	Value to store
     * @return	void
     */
    public function setFlashData($data, $value = NULL)
    {
        $this->set_userdata($data, $value);
        $this->mark_as_flash(is_array($data) ? array_keys($data) : $data);
    }

    // ------------------------------------------------------------------------

    /**
     * Keep flashdata
     *
     * Legacy CI_Session compatibility method
     *
     * @param	mixed	$key	SessionManager data key(s)
     * @return	void
     */
    public function keepFlashData($key)
    {
        $this->mark_as_flash($key);
    }

    // ------------------------------------------------------------------------

}
