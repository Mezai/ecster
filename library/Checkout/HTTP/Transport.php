<?php


class Ecster_HTTP_Transport
{

    /**
     * Create a new transport instance.
     *
     * @return Ecster_HTTP_CURLTransport
     */
    public static function create()
    {
        return new Ecster_HTTP_CURLTransport(
            new Ecster_CurlFactory
        );
    }
}
