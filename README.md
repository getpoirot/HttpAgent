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