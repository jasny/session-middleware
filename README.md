Jasny Session
===

[![Build Status](https://travis-ci.org/jasny/session-middleware.svg?branch=master)](https://travis-ci.org/jasny/{{library}})
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/session-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/{{library}}/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/session-middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/{{library}}/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a1a1745c-1272-46a3-9567-7bbb52acda5a/mini.png)](https://insight.sensiolabs.com/projects/a1a1745c-1272-46a3-9567-7bbb52acda5a)
[![BCH compliance](https://bettercodehub.com/edge/badge/jasny/session-middleware?branch=master)](https://bettercodehub.com/)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/session-middleware.svg)](https://packagist.org/packages/jasny/{{library}})
[![Packagist License](https://img.shields.io/packagist/l/jasny/session-middleware.svg)](https://packagist.org/packages/jasny/{{library}})

Using superglobals like `$_SESSION` object makes it difficult to test an application as global variables can have
unexpected side effects. Using superglobals undermines the effort of using dependecy injection and using containers.

The middleware creates an  can use `ext/session-middleware` or any other means to load and store session-middlewares. The superglobal
`$_SESSION` is only used in the middleware and can be disabled / removed for testing.

Installation
---

    composer require jasny/session-middleware

Usage
---

```php
$router->add(Jasny\SessionMiddleware());

$response = $router->handle($request);
```

Get the session object from the PSR-7 ServerRequest object and use it as array

```php
$session = $request->getAttribute('session');
$session['foo.bar'] = 10;

if (isset($session['foo.user'])) {
  // ...
}
```

Use `$session->abort()` to abort writing the changes and `$session->destroy()` to destroy the session.

Testing
---

When running tests, you can injecting a new `Session` object in the request before passing it to the middleware.

```php
$session = new Jasny\Session([
  'foo.user' => 'john@example.com'
]);

$response = $router->handler($request->withAttribute('session', $session));
```

The session middleware will ignore a session object that wasn't created by the middleware.
