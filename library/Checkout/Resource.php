<?php


abstract class EcsterResource
{
    protected $contentType = null;

    protected $accept = null;

    protected $location;

    public $data = array();

    protected $connector;


    public function __construct($connector)
    {
        $this->connector = $connector;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = (string)$location;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getAccept()
    {
        return $this->accept;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function setAccept($accept)
    {
        $this->accept = $accept;
    }

    public function parse(array $data)
    {
        $this->data = $data;
    }

    public function marshal()
    {
        return $this->data;
    }
}
