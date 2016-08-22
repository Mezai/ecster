<?php


class Ecster_HTTP_CURLTransport
{

    /**
     * DEFAULT_TIMEOUT
     */
    const DEFAULT_TIMEOUT = 10;

    /**
     * Factory for curl.
     *
     * @var Ecster_CurlFactory
     */
    protected $curl;


    /**
     * Timeout value in seconds.
     *
     * @var int
     */
    protected $timeout;


    /**
     * Options for cURL.
     *
     * @var array
     */
    protected $options;


    /**
     * Init new instance of Ecster_HTTP_CURLTransport.
     *
     * @param Ecster_CurlFactory $curl
     */
    public function __construct(Ecster_CurlFactory $curl)
    {
        $this->curl = $curl;
        $this->timeout = self::DEFAULT_TIMEOUT;
        $this->options = array();
    }

    /**
     * Set a curl option.
     *
     * @param string $option
     * @param string $value
     * @return void
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }


    /**
     * Set the timeout.
     *
     * @param int $timeout
     * @return void
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int)$timeout;
    }

    /**
     * Get the timout value.
     *
     * @return int $timeout
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Performs a HTTP request.
     *
     * @param Ecster_Http_Request $request to send.
     *
     * @throws RuntimeException
     * @throws Ecster_ConnectionErrorException
     * @return Ecster_HTTP_Response $response
     */
    public function send(Ecster_Http_Request $request)
    {
        $curl = $this->curl->handle();

        if ($curl === false) {
            throw new RuntimeException(
                'Failed to initialize a HTTP handle'
            );
        }

        $url = $request->getURL();
        
        $curl->setOption(CURLOPT_URL, $url['url']);

        $method = $request->getMethod();

        if ($method === 'POST') {
            $curl->setOption(CURLOPT_POST, true);
            $curl->setOption(CURLOPT_POSTFIELDS, $request->getData());
        }
        
        if ($method === 'PUT') {
            $curl->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
            $curl->setOption(CURLOPT_POSTFIELDS, $request->getData());
        }

        $requestHeaders = array();

        foreach ($request->getHeaders() as $key => $value) {
            $requestHeaders[] = $key . ': ' .$value;
        }

        $curl->setOption(CURLOPT_HTTPHEADER, $requestHeaders);

        $curl->setOption(CURLOPT_RETURNTRANSFER, true);

        $curl->setOption(CURLOPT_TIMEOUT, $this->timeout);

        $curlHeaders = new Ecster_CurlHeaders();

        

        $payload = $curl->execute();

        $info = $curl->getInfo();

        $error = $curl->getError();

        $curl->close();

        if ($payload === false || $info === false) {
            throw new Ecster_ConnectionErrorException(
                "Connection to '{url}' failed: {$error}"
            );
        }

        $headers = $curlHeaders->getHeaders();
       
        $headers['Content-Type'] = $info['content_type'];
        $response = new Ecster_HTTP_Response(
           $request, $headers, intval($info['http_code']), strval($payload)
       );
        return $response;
    }

    /**
     * Creates a HTTP request object.
     *
     * @param string $url
     * @return Ecster_Http_Request
     */
    public function createRequest($url)
    {
        return new Ecster_Http_Request($url);
    }
}
