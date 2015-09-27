<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class mem{

	private $_memcached;	// Holds the memcached object
	// ------------------------------------------------------------------------	

	/**
	 * Fetch from cache
	 *
	 * @param 	mixed		unique key id
	 * @return 	mixed		data on success/false on failure
	 */	
	public function get($id)
	{	
		$data = $this->_memcached->get($id);
		return (is_array($data)) ? $data[0] : FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Save
	 *
	 * @param 	string		unique identifier
	 * @param 	mixed		data being cached
	 * @param 	int			time to live
	 * @return 	boolean 	true on success, false on failure
	 */
	public function save($id, $data, $ttl = 60)
	{
		return $this->_memcached->set($id, array($data, time(), $ttl));
	}

	// ------------------------------------------------------------------------
	
	/**
	 * Delete from Cache
	 *
	 * @param 	mixed		key to be deleted.
	 * @return 	boolean 	true on success, false on failure
	 */
	public function delete($id)
	{
		return $this->_memcached->delete($id);
	}

	// ------------------------------------------------------------------------
	
	/**
	 * Clean the Cache
	 *
	 * @return 	boolean		false on failure/true on success
	 */
	public function clean()
	{
		return $this->_memcached->flush();
	}

	// ------------------------------------------------------------------------

	/**
	 * Setup memcached.
	 */
	public function __construct()
	{
		$this->_memcached = new Memcache();
        $this->_memcached->addServer('localhost', 11234) or die ("No memcache");
	}

}