<?php

namespace Webloopio\NetteWebsockets\Client;


interface IJWTAuthenticator extends IAuthenticator {

    const USER_ID_TOKEN_PAYLOAD_KEY = "userId";
    const USER_ROLES_TOKEN_PAYLOAD_KEY = "userRoles";

    public function verifyToken( string $token );
}