<?php

class Ecster_Http_Request
{
    /**
     * HTTP url
     *
     * @var string
     */
    protected $url;

    /**
     * HTTP Method
     *
     * @var string
     */
    protected $method;

    /**
     * HTTP headers
     *
     * @var array
     */
    protected $headers;

    /**
     * Payload
     *
     * @var string
     */
    public $data;

    /**
     * Init new Ecster_Http_Request instance.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->method = 'GET';
        $this->headers = array();
        $this->data = '';
    }

    /**
     * Set the HTTP Method used for the request.
     *
     *
     * @param string $method a HTTP method.
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Gets the HTTP method used for the request.
     *
     * @return string HTTP method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Gets the request url.
     *
     * @return string the request URL.
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Set header for the request.
     *
     * @param string $name the header name
     * @param mixed $value the header value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = strval($value);
    }


    /**
     * Get specifiv header for the request
     *
     * @param string $name the header name
     * return string|null the header value
     */

    public function getHeader($name)
    {
        if (!array_key_exists($name, $this->headers)) {
            return null;
        }

        return $this->headers[$name];
    }

    /**
     * Gets the headers specified for the request.
     *
     * @return array headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }


    /**
     * Sets the data (payload) for the request.
     *
     * @param string $data the request payload.
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the data (payload) for the request.
     *
     * @return string the request payload
     */
    public function getData()
    {
        return $this->data;
    }
}
