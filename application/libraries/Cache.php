<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Cache
{
    private $_CI;
    private $_cache_path = '';
    private $_ext = '';



    public function __construct()
    {
        $this->_CI =& get_instance();

        $this->_cache_path = APPPATH . 'cache/';

        if($this->_CI->config->item('cache_path') != '')
        {
            $this->_cache_path = trim($this->_CI->config->item('cache_path'), '/') . '/';
        }

        $this->_CI->load->helper('file');
    }

    public function get($id)
    {
        $path = $this->_cache_path . $this->get_cache_file_name($id) . $this->_ext;

        if(!file_exists($path))
        {
            return FALSE;
        }

        $data = file_get_contents($path);

        if(!$data)
        {
            return FALSE;
        }

        $data = json_decode($data, TRUE);

        if (time() >  $data['time'] + $data['ttl'])
        {
            unlink($path);
            return FALSE;
        }

        return $data['data'];
    }

    public function delete($id)
    {
        if(file_exists($path = $this->_cache_path . $this->get_cache_file_name($id) . $this->_ext))
        {
            return unlink($path);
        }

        return FALSE;
    }

    public function ignore_save($id, $data, $time = 31536000)
    {
        $contents = array(
            'time' => time(),
            'data' => $data,
            'ttl'  => $time,
        );

        $path = $this->_cache_path . $this->get_cache_file_name($id) . $this->_ext;

        if(file_put_contents($path, json_encode($contents)))
        {
            chmod($path, 0777);
            return TRUE;
        }

        return FALSE;
    }

    public function save($id, $data, $time = 31536000)
    {
        if($this->_CI->config->item('cache_allow') === FALSE)
        {
            return;
        }

        $contents = array(
            'time' => time(),
            'data' => $data,
            'ttl'  => $time,
        );

        $path = $this->_cache_path . $this->get_cache_file_name($id) . $this->_ext;

        if(file_put_contents($path, json_encode($contents)))
        {
            chmod($path, 0777);
            return TRUE;
        }

        return FALSE;
    }

    public function clean()
    {
        return delete_files($this->_cache_path);
    }

    public function get_cache_file_name($id)
    {
        return md5($id . config('encryption_key'));
    }
}
