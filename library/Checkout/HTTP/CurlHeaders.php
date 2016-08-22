<?php

class Ecster_CurlHeaders
{

  /**
   * Curl headers.
   *
   * @var array
   */
  protected $headers;

    public function __construct()
    {
        $this->headers = array();
    }


    public function processHeader($curl, $header)
    {
        $curl = null;
        //TODO replace with regexp, e.g. /^([^:]+):([^:]*)$/ ?
        $pos = strpos($header, ':');
        // Didn't find a colon.
        if ($pos === false) {
            // Not real header, abort.
            return strlen($header);
        }
        $key = substr($header, 0, $pos);
        $value = trim(substr($header, $pos+1));
        $this->headers[$key] = trim($value);
        return strlen($header);
    }

  /**
   * Get headers.
   *
   * @return array $headers
   */
  public function getHeaders()
  {
      return $this->headers;
  }
}
