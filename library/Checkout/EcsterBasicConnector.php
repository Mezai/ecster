<?php

class EcsterBasicConnector
{

    /**
     * Ecster_Checkout_HTTP_Transport
     *
     * @var Ecster_Checkout_HTTP_Transport
     */
    protected $http;

    /**
     * The domain for the request.
     *
     * @var string
     */
    protected $domain;

    /**
     * Ecster username
     *
     * @var string
     */
    protected $username;

    /**
     * Ecster password
     *
     * @var string
     */
    protected $password;

    /**
     * Init a new EcsterBasicConnector instance
     *
     * @param mixed $http
     * @param string $username
     * @param string $password
     * @param string $domain
     */
    public function __construct($http, $username, $password, $domain = EcsterConnector::BASE_URL)
    {
        $this->http = $http;
        $this->username = $username;
        $this->password = $password;
        $this->domain = $domain;
    }

    /**
     * Throw an exception if the server responds with an error code.
     *
     * @param Ecster_HTTP_Response $response
     * @throws Ecster_ApiErrorException
     * @return void
     */
    protected function verifyResponse(Ecster_HTTP_Response $response)
    {
        if ($response->getStatus() >= 400 && $response->getStatus() <= 599) {
            $json = json_decode($response->getData(), true);
            $payload = ($json && is_array($json)) ? $json : array();
            throw new Ecster_ApiErrorException(
                'Api error', 20
            );
        }
    }

    /**
     * Handle the response.
     *
     * @param Ecster_HTTP_Response $result
     * @param EcsterRecource $resource
     * @param array $visited
     */
    protected function handleResponse(Ecster_HTTP_Response $result, $resource, array $visited = array())
    {
        $this->verifyResponse($result);

        $url = $result->getHeader('Location');

        switch ($result->getStatus()) {
          case 201:
            $resource->setLocation($url);
            break;
        case 200:
            $json = json_decode($result->getData(), true);
            if ($json === null) {
                throw new Ecster_ConnectorErrorException(
                    'Bad format on response content.',
                    -2
                );
            }
            $resource->parse($json);
        }

        return $result;
    }


    /**
     * Get the data to use
     *
     * @param EcsterResource $resource
     * @param array $options
     * @return array data to use for HTTP requests
     */
    public function getData($resource, array $options)
    {
        if (array_key_exists('data', $options)) {
            return $options['data'];
        }

        return $resource->marshal();
    }


    /**
     * Apply the method on the resource
     *
     * @param string $method
     * @param EcsterResource $resource
     * @param array $options
     * @return mixed
     */
    public function apply($method, $resource, array $options = null)
    {
        switch ($method) {
            case 'GET':
            case 'POST':
            case 'PUT':
                return $this->handle($method, $resource, $options, array());
            default:
                throw new InvalidArgumentException(
                    "{$method} is not a valid HTTP method"
                );
        }
    }


    /**
     * Get the current domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the url to use
     *
     * @param mixed $resource
     * @param array $options
     *
     * @return string Url to use for HTTP requests
     */
    public function getUrl($resource, array $options)
    {
        return $resource->getLocation();
    }

    /**
     * Set content (headers, payload) on a request
     *
     * @param EcsterResource $resource
     * @param string $method
     * @param string $payload
     * @param string $url
     *
     * @return Ecster_HTTP_Request
     */
    protected function createRequest($resource, $method, $payload, $url)
    {
        $request = $this->http->createRequest($url);

        $request->setMethod($method);

        $accept = $resource->getAccept();

        $contentType = $resource->getContentType();

        $request->setHeader('X-Ecster-origin', 'checkout');
        $request->setHeader('X-Ecster-username', $this->username);

        $request->setHeader('X-Ecster-password', $this->password);
        $request->setHeader('Content-Type', $contentType);

        if (strlen($payload) > 0) {
            $request->setData($payload);
        }

        return $request;
    }

    /**
     * Perform a HTTP call on the supplied resource
     *
     * @param string $method
     * @param EcsterResource $resource
     * @param array $options
     * @param array $visited
     *
     * @throws Ecster_Exception
     * @return Ecster_HTTP_Response
     */
    protected function handle($method, $resource, array $options, array $visited = array())
    {
        if ($options === null) {
            $options = array();
        }

        $url = $this->getUrl($resource, $options);

        $payload = '';
        if ($method === 'POST' || $method === 'PUT') {
            $payload = json_encode($this->getData($resource, $options));
        }

        $request = $this->createRequest($resource, $method, $payload, $options);

        $result = $this->http->send($request);

        return $this->handleResponse($result, $resource, $visited);
    }
}
