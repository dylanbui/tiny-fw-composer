<?php
/**
 * CodeIgniter Caching Library
 *
 * A fast file based caching library which supports tagging.
 *
 * @package		CodeIgniter
 * @author		Alex Bilbie | www.alexbilbie.com | alex@alexbilbie.com
 * @copyright	Copyright (c) 2009, Alex Bilbie.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://alexbilbie.com/code/
 * @version		Version 0.2
 */
 
 /**
 *	Usage
 *
 *		$cache = new Cache($config->config_values['cache']);
 *		$registry->oCache = $cache;	
 *	
 *	Add something to the cache (returns TRUE if successful)
 *
 *		$this->oCache->set( (string)$id, (mixed)$data, (array)$tags, (int)$lifetime )
 *
 *	Get something from the cache (returns FALSE if fail)
 *
 *		$this->oCache->get( (string)$id );
 *
 *	Delete an item
 *
 *		$this->oCache->delete( (string)$id );
 *
 *	Delete an item by tag
 *
 *		$this->oCache->delete_by_tag( (string|array)$tags );
 *
 *	Delete all
 *
 *		$this->oCache->delete_all();
 *
 *	Cleanup dead files (intended for use in cron/scheduled job)
 *
 *		$this->oCache->cleanup();
 *
 */

namespace TinyFw;

use TinyFw\Helper\File as FileHandler;

class Cache {

	var $_life_time = 600; // 10 minute
	var $_cache_path;
	var $_cache_handler_filepath;

    private $_fileHandler = NULL;

    public function __construct($cfg)
	{
        $this->_fileHandler = new FileHandler();
		
		$path = '';
		if (isset($cfg['cache_path']))
			$path = $cfg['cache_path'];
		
		$life_time = '';
		if (isset($cfg['life_time']))
			$life_time = $cfg['life_time'];
		
		$this->_life_time = ($life_time == '') ? 600 : intval($life_time);
		$this->_cache_path = ($path == '') ? __SITE_PATH.'/public_html/cache/' : $path;
		
		// Setup cache handler
		$this->_cache_handler_filepath = $this->_cache_path.'cache_handler';
	}
	
	public function set($id, $data, $tags = array(), $lifetime = NULL)
	{
		// Set lifetime
		if($lifetime === NULL)
		{
			$lifetime = $this->_life_time;
		}
		
		// Serialize the data for saving
		$data = serialize($data);
		
		// Make a unique MD5
		$file_name = md5(time().$id);
		$info['CACHE_NAME'] = $file_name;
		
		// The ID
		$info['CACHE_ID'] = $id;
		
		// Log the lifetime
		$info['CACHE_LIFETIME'] = $lifetime;
		
		// Log the time of creation
		$info['CACHE_BIRTH'] = time();
		
		// Log the tags
		$info['CACHE_TAGS'] = $tags;
				
		// Write the data to a cache file
		$cache_filepath = $this->_cache_path.$file_name;
		if(!$this->_fileHandler->write_file($cache_filepath, $data))
		{
            throw new \Exception("Unable to write cache file: ".$cache_filepath);
		}
		
		// Save to cache handler
		if($this->_save_to_handler($id, $info, $tags)){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function get($id)
	{
		// Get cache handler
		$handler = $this->_read_handler();
		
		// Is the ID a registered cache file?
		if(!isset($handler['cache_files'][$id])){
			return NULL;
		}
		
		$file = $handler['cache_files'][$id];
		
		// Get the UID of the cache file
		$uid = $file['CACHE_NAME'];
				
		// Get the lifetime of the file
		$lifetime = $file['CACHE_LIFETIME'];
		
		// Get the time of creation
		$birth = $file['CACHE_BIRTH'];
		
		// Is the file dead? - I think this is faster than testing time() against filemtime()
		$time_since_creation = (time() - $birth);
		
		if($time_since_creation < $lifetime){
			
			// File is alive
			$cached_data = $this->_fileHandler->read_file($this->_cache_path.$uid);
			
			if(!$cached_data){
			
				// File doesn't exist or can't be read
				$handler = $this->_delete_file($id);
				$this->_save_handler($handler);
				return FALSE;
				
			} else {
				
				// Return the data
				return unserialize($cached_data);
			
			}
			
		} else {
		
			// File is dead so let's remove it's handle and the file
			$handler = $this->_delete_file($id);
			$this->_save_handler($handler);
			return NULL;
			
		}
	}
	
	public function delete_all()
	{
		// Get cache handler
		$handler = $this->_read_handler();
		
		// If there are handles
		if(count($handler['cache_files'] > 0)){
			
			// For each handle delete it's file
			foreach($handler['cache_files'] as $cf)
			{
				@unlink($this->_cache_path.$cf['CACHE_NAME']);
			}
			
			// Write a new cache handler
			$this->_new_handler();
		}
		
	}
	
	public function delete_by_tag($tags)
	{
		// Open and read the cache handler
		$handler = $this->_read_handler();
		
		// If there are handles
		if(count($handler['cache_files']) > 0){
		
			if(is_array($tags) && count($tags) > 0){
				
				// Foreach tag, see if it exists, if so delete all files and handles linked to it then delete the tag
				foreach($tags as $tag){
					
					if(isset($handler['cache_tags'][$tag])){
						
						foreach($handler['cache_tags'][$tag] as $id=>$file)
						{	
							$handler = $this->_delete_file($id);
							$this->_save_handler($handler);
						}
						
					}
					
				}
				
			} else {
				
				if(isset($handler['cache_tags'][$tags])){
					
					// See if it exists, if so delete all files and handles linked to it then delete the tag
					foreach($handler['cache_tags'][$tags] as $id=>$file)
					{
						$handler = $this->_delete_file($id);
						$this->_save_handler($handler);
					}

				}
				
			}
		
		}
	}
	
	public function delete($id)
	{
		// Get cache handler
		$handler = $this->_read_handler();
		
		// If the handle doesn't exist then just return NULL
		if(!isset($handler['cache_files'][$id])){
			return NULL;
		} else {
			// The handle does exist so delete the file and the handle
			$handler = $this->_delete_file($id);
			
			// Save the cache handler
			if($this->_save_handler($handler)){
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	
	public function cleanup()
	{
		// Get cache handler
		$handler = $this->_read_handler();
		
		// For each file, check if it's dead, if so remove it.
		if(count($handler['cache_files'])){
		
			// This function rebuilds the tag array
			$new_tags = array();
			
			foreach($handler['cache_files'] as $file)
			{
				// Test age
				$time_since_creation = (time() - $file['CACHE_BIRTH']);
				if($time_since_creation > $file['CACHE_LIFETIME']){

					// Remove the physical file
					@unlink($this->_cache_path.$file['CACHE_ID']);
					
					// Remove the handle reference
					unset($handler['cache_files'][$file['CACHE_ID']]);
					
				} else {
				
					// File is still alive so let's add it's tags to the new tag array
					if(count($file['CACHE_TAGS']) > 0){
						foreach($file['CACHE_TAGS'] as $tag)
						{
							$new_tags[$tag][$file['CACHE_ID']] = $file['CACHE_NAME'];
						}
					}
									
				}
			}
			
			// Overwrite the old tags
			$handler['cache_tags'] = $new_tags;
			
			// Save
			$this->_save_handler($handler);
		
		}
	}
	
	private function _new_handler()
	{
		// Write a new cache handler
		$handler = serialize(array("cache_files"=>array(), "cache_tags"=>array()));
		if(!$this->_fileHandler->write_file($this->_cache_handler_filepath, $handler))
		{
//            throw new \Exception("Unable to write a new handler: ".$this->_cache_handler_filepath);
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	private function _save_to_handler($id, $info, $tags)
	{
		// Open the cache handler
		$cache_handler = $this->_fileHandler->read_file($this->_cache_handler_filepath);
		
		// If we can't open the cache handler then write a new one
		if(!$cache_handler){
			$handler = serialize(array("cache_files"=>array(), "cache_tags"=>array()));
			if(!$this->_fileHandler->write_file($this->_cache_handler_filepath, $handler))
			{
                throw new \Exception("Unable to open cache handler or write a new handler: ".$this->_cache_handler_filepath);
			}
		}
		
		// Unserialize the cache handler data
		$handler = unserialize($cache_handler);
		
		// If the key already exists then overwrite it
		if(isset($handler["cache_files"][$id])){
		
			// Delete the old file and handle
			@unlink($this->_cache_path.$handler["cache_files"][$id]['CACHE_NAME']);
			unset($handler['cache_files'][$id]);
			
		}
		
		// Add the new handle
		$handler["cache_files"][$id] = $info;
		
		// Add the tags
		if(count($tags) > 0){
			foreach($tags as $tag)
			{
				if(isset($handler["cache_tags"][$tag])){
					$handler["cache_tags"][$tag][$id] = $info['CACHE_NAME'];
				} else {
					$handler["cache_tags"][$tag] = array($id=>$id);
				}
			}
		}
		
		if($this->_save_handler($handler)){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private function _save_handler($handler)
	{
// 		write_file($this->_cache_handler_filepath.'.txt', print_r($handler,TRUE));
		$handler = serialize($handler);
		if(!$this->_fileHandler->write_file($this->_cache_handler_filepath, $handler)){
//            throw new \Exception("Unable to save the handler: ".$this->_cache_handler_filepath);
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	private function _read_handler()
	{
		$cache_handler = $this->_fileHandler->read_file($this->_cache_handler_filepath);
		if(!$cache_handler){
//            throw new \Exception("Unable to open cache handler: ".$this->_cache_handler_filepath);
			return NULL;
		} else {
			$handler = unserialize($cache_handler);
			return $handler;
		}
	}
	
	private function _delete_file($id)
	{
		$handler = $this->_read_handler();
		
		// Remove the physical file
		@unlink($this->_cache_path.$handler['cache_files'][$id]['CACHE_NAME']);
		
		// Tags to remove links from
		$tags = $handler['cache_files'][$id]['CACHE_TAGS'];
		
		// Remove the handle reference
		unset($handler['cache_files'][$id]);
		
		if(count($tags) > 0){
			foreach($tags as $tag)
			{
				if(isset($handler['cache_tags'][$tag][$id])){
					unset($handler['cache_tags'][$tag][$id]);
					if(count($handler['cache_tags'][$tag]) == 0){
						unset($handler['cache_tags'][$tag]);
					}
				}
			}
		}
		
		return $handler;
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Info
	 *
	 * Not supported by file-based caching
	 *
	 * @param 	string	user/filehits
	 * @return 	mixed 	FALSE
	 */
	public function cache_info($type = NULL)
	{
		return $this->_fileHandler->get_dir_file_info($this->_cache_path);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Cache Metadata
	 *
	 * @param 	mixed		key to get cache metadata on
	 * @return 	mixed		FALSE on failure, array on success.
	 */
	public function get_metadata($id)
	{
		if ( ! file_exists($this->_cache_path.$id))
		{
			return FALSE;
		}

		$data = $this->_fileHandler->read_file($this->_cache_path.$id);
		$data = unserialize($data);

		if (is_array($data))
		{
			$mtime = filemtime($this->_cache_path.$id);

			if ( ! isset($data['ttl']))
			{
				return FALSE;
			}

			return array(
				'expire'	=> $mtime + $data['ttl'],
				'mtime'		=> $mtime
			);
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------
	
	/**
	 * Is supported
	 *
	 * In the file driver, check to see that the cache directory is indeed writable
	 *
	 * @return boolean
	 */
	public function is_supported()
	{
		return $this->_fileHandler->is_really_writable($this->_cache_path);
	}
	
	// ------------------------------------------------------------------------
	
	
}