<?php
namespace Poirot\HttpAgent\Platform\Plugin;

/**
 * User agents MAY ignore Set-Cookie headers contained in
 * responses with 100-level status codes but MUST process Set-Cookie
 * headers contained in other responses (including responses with 400-
 * and 500-level status codes)
 *
 * == Server -> User Agent ==
 * Set-Cookie: SID=31d4d96e407aad42; Path=/; Secure; HttpOnly
 * Set-Cookie: lang=en-US; Path=/; Domain=example.com
 * == User Agent -> Server ==
 * Cookie: SID=31d4d96e407aad42; lang=en-US
 *
 *
 */

class CookieBrowserPlg
{
    // TODO Implement
}
