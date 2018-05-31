<?php

namespace Webloopio\NetteWebsockets\Client;


interface IJWTAuthenticator extends IAuthenticator {

    const USER_ID_TOKEN_PAYLOAD_KEY = "user_id";
    const USER_ROLES_TOKEN_PAYLOAD_KEY = "user_roles";

    public function verifyToken( string $token );
}