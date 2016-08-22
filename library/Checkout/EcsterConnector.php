<?php


class EcsterConnector
{

    /**
     * Domain of the live system.
     */
    const BASE_URL = 'https://secure.ecster.se/rest';

    /**
     * Domain of the test system.
     */
    const TEST_URL = 'https://labs.ecster.se/rest';

    public static function create($username, $password, $domain = self::BASE_URL)
    {
        return new EcsterBasicConnector(
            Ecster_HTTP_Transport::create(),
            $username,
            $password,
            $domain

        );
    }
}
