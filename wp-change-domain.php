#!/usr/bin/php
<?php
/**
 * Author: Daniel Doezema
 * Contributor: Alon Peer, Christoph Herbst
 * Author URI: http://dan.doezema.com
 * Version: 1.0 (fork)
 * Description: This script was developed to help ease migration of WordPress sites from one domain to another.
 *
 * Copyright (c) 2010, Daniel Doezema
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *   * The names of the contributors and/or copyright holder may not be
 *     used to endorse or promote products derived from this software without
 *     specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL DANIEL DOEZEMA BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright Copyright (c) 2010 Daniel Doezema. (http://dan.doezema.com)
 * @license http://dan.doezema.com/licenses/new-bsd New BSD License
 */

/* == CONFIG ======================================================== */

$wordpress_install_dir='./wordpress';

if ( !defined('ABSPATH') ) {
	/** Set up WordPress environment */
	require_once($wordpress_install_dir.'/wp-load.php');
}


/* == NAMESPACE CLASS =============================================== */

class DDWordPressDomainChanger {

    /**
     * Actions that occurred during request.
     *
     * @var array
     */
    public $actions = array();

    /**
     * Notices that occurred during request.
     *
     * @var array
     */
    public $notices = array();

    /**
     * Errors that occurred during request.
     *
     * @var array
     */
    public $errors = array();

    /**
     * MySQLi Object
     *
     * @var string
     */
    private $mysqli = null;

    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Gets $table_prefix value from the wp-config.php file (if loaded).
     *
     * @return string;
     */
    public function getConfigTablePrefix() {
	global $wpdb;
	return $wpdb->prefix;
    }

    /**
     * Gets the "siteurl" WordPress option (if possible).
     *
     * @return mixed; false if not found.
     */
    public function getOldDomain() {
            $mysqli = $this->getDatabase();
            if(mysqli_connect_error()) {
                $this->notices[] = 'Unable to connect to this server\'s database using the settings from wp-config.php; check that it\'s properly configured.';
            } else {
                $result = $mysqli->query('SELECT * FROM '.$this->getConfigTablePrefix().'options WHERE option_name="siteurl";');
                if(is_object($result) && ($result->num_rows > 0)) {
                    $row = $result->fetch_assoc();
                    return str_replace('http://','', $row['option_value']);
                } else {
                    $this->error[] = 'The WordPress option_name "siteurl" does not exist in the "'.$this->getConfigTablePrefix().'options" table!';
                }
            }
        return false;
    }


    /**
     * Overrides the class self::$mysqli property with a different MySQLi instance.
     *
     * @return void;
     */
    public function setDatabase(mysqli $mysqli) {
        $this->mysqli = $mysqli;
    }

    /**
     * Attempts to lazy load a connection to the mysql database based on the config file.
     *
     * @return mixed; MySQLi instance, false on failure to connect.
     */
    public function getDatabase() {
        if($this->mysqli === null) {
           $this->mysqli = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
                if(mysqli_connect_error()) {
                    $this->notices[] = 'Unable to connect to this server\'s database using the settings from wp-config.php; check that it\'s properly configured.';
                    $this->mysqli = false;
                }
        }
        return ($this->mysqli instanceof mysqli) ? $this->mysqli : false;
    }
}

/* == START PROCEDURAL CODE ============================================== */

/**
 * Recursive alternative to str_replace that supports replacing keys as well
 *
 * @param string  $search
 * @param string  $replace
 * @param array   $array
 * @param boolean $keys_too
 *
 * @return array
 */
function replaceTree($search="", $replace="", $array=false, $keys_too=false)
{
  if (!is_array($array)) {
    // Regular replace
    return str_replace($search, $replace, $array);
  }
 
  $newArr = array();
  foreach ($array as $k=>$v) {
    // Replace keys as well?
    $add_key = $k;
    if ($keys_too) {
      $add_key = str_replace($search, $replace, $k);
    }
 
    // Recurse
    $newArr[$add_key] = replaceTree($search, $replace, $v, $keys_too);
  }
  return $newArr;
}

try{
	$DDWPDC = new DDWordPressDomainChanger();
	$POST["prefix"] = $DDWPDC->getConfigTablePrefix(); 
	$POST["old_domain"] = $DDWPDC->getOldDomain();
	$POST['new_domain'] = $argv[1];

            if( !isset($argv[1]) ) {
                // Let them correct this instead of assuming it's correct and removing the "http://".
                throw new Exception('Please specify a new domain!');
            }
            // Check for "http://" in the new domain
            if(stripos($POST['new_domain'], 'http://') !== false) {
                // Let them correct this instead of assuming it's correct and removing the "http://".
                throw new Exception('The "New Domain" field must not contain "http://"');
            }

            // DB Connection
            $mysqli = $DDWPDC->getDatabase();
            if(mysqli_connect_error()) {
                throw new Exception('Unable to create database connection; most likely due to incorrect connection settings.');
            }


            // Escape $_POST data for sql statements.
            $data = array();
            foreach($POST as $key => $value) {
                $data[$key] = $mysqli->escape_string($value);
            }

            // Update Options
	    $options = get_alloptions();
	    foreach( $options as $name => $option){
	       if ( false === strpos($option,$data['old_domain']))
		continue;
	       $optObj = get_option($name);
	       $newObj = replaceTree($data['old_domain'],$data['new_domain'],$optObj);
	       update_option($name,$newObj);
	    }
            $DDWPDC->actions[] = 'Old domain ('.$data['old_domain'].') replaced with new domain ('.$data['new_domain'].') in '.$data['prefix'].'options.option_value';

            // Update Post Content
            if(!$mysqli->query('UPDATE '.$data['prefix'].'posts SET post_content = REPLACE(post_content,"'.$data['old_domain'].'","'.$data['new_domain'].'");')) {
                throw new Exception($mysqli->error);
            }
            $DDWPDC->actions[] = 'Old domain ('.$data['old_domain'].') replaced with new domain ('.$data['new_domain'].') in '.$data['prefix'].'posts.post_content';

            // Update Post GUID
            if(!$mysqli->query('UPDATE '.$data['prefix'].'posts SET guid = REPLACE(guid,"'.$data['old_domain'].'","'.$data['new_domain'].'");')) {
                throw new Exception($mysqli->error);
            }
            $DDWPDC->actions[] = 'Old domain ('.$data['old_domain'].') replaced with new domain ('.$data['new_domain'].') in '.$data['prefix'].'posts.guid';

            // Update "upload_path"
            $upload_dir = realpath($wordpress_install_dir).'/wp-content/uploads';
            if(!$mysqli->query('UPDATE '.$data['prefix'].'options SET option_value = "'.$upload_dir.'" WHERE option_name="upload_path";')) {
                throw new Exception($mysqli->error);
            }
            $DDWPDC->actions[] = 'Option "upload_path" has been changed to "'.$upload_dir.'"';

            // Delete "recently_edited" option. (Will get regenerated by WordPress)
            if(!$mysqli->query('DELETE FROM '.$data['prefix'].'options WHERE option_name="recently_edited";')) {
                throw new Exception($mysqli->error);
            }
            $DDWPDC->actions[] = 'Option "recently_edited" has been deleted -> Will be regenerated by WordPress.';

            // Update User Meta
            if(!$mysqli->query('UPDATE '.$data['prefix'].'usermeta SET meta_value = REPLACE(meta_value,"'.$data['old_domain'].'","'.$data['new_domain'].'");')) {
                throw new Exception($mysqli->error);
            }
            $DDWPDC->actions[] = 'Old domain ('.$data['old_domain'].') replaced with new domain ('.$data['new_domain'].') in '.$data['prefix'].'usermeta.meta_value';

    } catch (Exception $exception) {
        $DDWPDC->errors[] = $exception->getMessage();
    }


    if(count($DDWPDC->errors) > 0): foreach($DDWPDC->errors as $error):
	echo "Error:".$error."\n";
    endforeach; endif;

    if(count($DDWPDC->notices) > 0): foreach($DDWPDC->notices as $notice):
	echo "Notice:".$notice."\n";
    endforeach; endif;

    if(count($DDWPDC->actions) > 0): foreach($DDWPDC->actions as $action):
	echo "Action:".$action."\n";
    endforeach; endif; 

    
?>

