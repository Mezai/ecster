<?php

class EcsterOrder extends EcsterResource
{

    /**
     * Path that is used to create resources.
     *
     * @var string
     */
    protected $relativePath = '/rest/eps/v1/cart';

    /**
     * contentType
     *
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * Init a new EcsterOrder instance.
     *
     * @param EcsterConnector $connector
     * @param string $internalReference
     */
    public function __construct($connector, $internalReference = null)
    {
        parent::__construct($connector);

        if ($internalReference !== null) {
            $uri = $this->connector->getDomain() . "{$this->relativePath}/{$internalReference}";
            $this->setLocation($uri);
        }
    }

    /**
    * Get Ecster cartKey
    *
    * @return string cartKey
    */
    public function getCartKey()
    {
        return $this->data['response']['key'];
    }

    /**
     * Get the response
     *
     * return string
     */
    public function getResponse()
    {
        return $this->data['response'];
    }

    /**
     * Create a new order.
     *
     * @param array $data
     * @return void
     */
    public function create(array $data)
    {
        $options = array(
          'url' => $this->connector->getDomain() . $this->relativePath,
          'data' => $data
        );

        $this->connector->apply('POST', $this, $options);

        return $this;
    }

    /**
     * Update a order.
     *
     * @param array $data
     * @return void
     */
    public function update(array $data)
    {
        $options = array(
            'url' => $this->location,
            'data' => $data
        );

        $this->connector->apply('PUT', $this, $options);

        return $this;
    }
    /**
     * Fetch a order.
     *
     * 
     * @return EcsterOrder
     */
    
    public function fetch()
    {
        $options = array(
            'url' => $this->location
        );

        $this->connector->apply('GET', $this, $options);

        return $this;
    }
}
