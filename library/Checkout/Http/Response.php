<?php


class Ecster_HTTP_Response
{

    /**
     * HTTP response status code.
     *
     * @var int
     */
    protected $status;


    /**
     * Request object.
     *
     * @var Ecster_HTTP_Request
     */
    public $request;


    /**
     * HTTP headers.
     *
     * @var array
     */
    protected $headers;


    /**
     * Data.
     *
     * @var string
     */
    protected $data;

    /**
     * Init new instance of the Ecster_HTTP_Response
     *
     * @param Ecster_Http_Request $request
     * @param array $headers
     * @param string $status
     * @param string $data
     */
    public function __construct(Ecster_Http_Request $request, array $headers, $status, $data)
    {
        $this->request = $request;
        $this->headers = array();
        foreach ($headers as $key => $value) {
            $this->headers[strtolower($key)] = $value;
        }
        $this->status = $status;
        $this->data = $data;
    }


    /**
     * Gets the HTTP status code.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the HTTP request that this response originated from.
     *
     * @return obj
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Get data.
     *
     * @return string response payload
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get all headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get a specific header
     *
     * @param string $name
     * @return string|null if the header does not exists.
     */
    public function getHeader($name)
    {
        $name = strtolower($name);
        if (!array_key_exists($name, $this->headers)) {
            return null;
        }

        return $this->headers[$name];
    }
}
