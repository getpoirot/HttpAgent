# HttpAgent

```php
$browser = new Browser('http://192.168.123.171/', [
    'request' => [
        'headers' => [
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
        ]
    ]
]);

$result = $browser->POST('/api/v1/auth/login'
    , [ 'form_data'
        => [
            'email'    => 'naderi.payam@gmail.com',
            'password' => '123456'
        ]
    ]
);

$result->expected(function(HttpResponse $httpResponse) use ($browser) {
    $jsonResult = $httpResponse->plg()->json();
    $authHeader = HeaderFactory::factory('Authorization', 'Bearer '.$jsonResult->token);
    $browser->inOptions()->getRequest()->getHeaders()->set(
        $authHeader
    );
});

$browser->GET('/api/v1/users/me', null, [
    'Accept' => 'application/json, text/javascript, */*; q=0.01',
    'X-Requested-With' => 'XMLHttpRequest',
    'Referer' => 'http://localhost:8080/'
])->expected(function($response) {
    $response->flush(false);
});
```

```php
$request =
        "GET /payam/ HTTP/1.1\r\n"
        ."Host: 95.211.189.240\r\n"
        ."User-Agent: AranRojan-PHP/5.5.9-1ubuntu4.11\r\n"
        ."Accept-Encoding: gzip\r\n"
        ."Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n"
        ."Cache-Control: no-cache"
    ;
    $browser = new Browser('http://95.211.189.240/', ['connection' => ['allow_decoding' => false]]);
    $res = $browser->request(new HttpRequest($request));

    echo ($res->getRawBody()->read());
```
