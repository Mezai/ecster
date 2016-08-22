<?php

class Ecster_CurlHandle
{

    /**
     * Curl handle.
     *
     * @var mixed
     */
    private $_handle = null;


    /**
     * Init a new Ecster_CurlHandle instance.
     *
     * @throws RuntimeException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException(
                'cURL extension is required'
            );
        }

        $this->_handle = curl_init();
    }
    

    /**
     * Set a option for the curl transfer.
     *
     * @param int $name
     * @param mixed $value
     */
    public function setOption($name, $value)
    {
        curl_setopt($this->_handle, $name, $value);
    }

    /**
     * Perform the cURL session
     *
     * @return mixed response
     */
    public function execute()
    {
        return curl_exec($this->_handle);
    }

    /**
     *  Get information regarding transfer.
     *
     * @return array
     */
    public function getInfo()
    {
        return curl_getinfo($this->_handle);
    }

    /**
     * Return a string containing the last error for the current session
     *
     * @return string Error message
     */
    public function getError()
    {
        return curl_error($this->_handle);
    }

    /**
     * Close a cURL session
     *
     * @return void
     */
    public function close()
    {
        curl_close($this->_handle);
    }
}
