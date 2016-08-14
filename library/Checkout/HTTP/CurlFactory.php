<?php


class Ecster_CurlFactory
{
    /**
     * Create a new cURL handle.
     *
     * @return Ecster_CurlHandle 
     */
    public function handle()
    {
        return new Ecster_CurlHandle();
    }
}
