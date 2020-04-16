Session middleware
===

[![Build Status](https://travis-ci.org/jasny/session-middleware.svg?branch=master)](https://travis-ci.org/jasny/session-middleware)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/session-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/session-middleware/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/session-middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/session-middleware/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/session-middleware.svg)](https://packagist.org/packages/jasny/session-middleware)
[![Packagist License](https://img.shields.io/packagist/l/jasny/session-middleware.svg)](https://packagist.org/packages/jasny/session-middleware)

Using superglobals like `$_SESSION` object makes it difficult to test an application as global variables can have
unexpected side effects. Using superglobals undermines the effort of using dependency injection and using containers.

The middleware creates an object that wraps `$_SESSION`, which is available for dependency injection and as attribute
of the PSR-7 `ServerRequest`. The middleware complies with [PSR-15](https://www.php-fig.org/psr/psr-15/). It will also
work as double pass middleware.

Installation
---

    composer require jasny/session-middleware

Usage
---

```php
use Jasny\Session\SessionMiddleware;

$router->add(new SessionMiddleware());
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

### Methods
* `start()` - Start the session.
* `status()` - Get the session status.
* `stop()` - Write session data and end session.
* `abort()` - Discard session array changes and finish session.
* `clear()` - Clear all data from the session.
* `kill()` - Destroy the session and remove the session cookie.
* `rotate()` - Delete the current session and start a new one.

When rotating a session, it's possible to copy some of the data by supplying a callback.

```php
$session->rotate(fn(array $oldSessionData) => ['tid' => $oldSessionData['tid'] ?? null]);
```

### Flash

The session flash object can be used to pass a message to the next request. It is automatically removed from the session
after it is used. A typical use case is to store information in a database, than redirect to a page and showing a
success message. Or if the information could not be saved, to show an error message.

The flash information contains a type (e.g. `success`, `error`, `info`, `warning`) and a message. Optionally a
content type can be specified for the message. This defaults to `text/plain`.

```php
$session->flash('success', 'The information has been saved');
```

In the next request

```twig
{% for flash in app.flashes() %}
    <div class="flash-{{ flash.type }}">
        {{ flash.message }}
    </div>
{% endfor %}
```

If `flash()` or `flashes()` is called, the flash messages are cleared from the session. To prevent this call
`reissue()`

```php
$session->flashes()
    ->reissue()
    ->add('warning', "Could not display the page");

header('Location: /other-page');
exit();
```

Call `$session->flashes()->clear()` to explicly clear all flash messages, both newly added (to the session) and those
available for the current request.


Testing
---

When running tests, you can injecting a `MockSession` object in the server request before passing it to the middleware.

```php
use Jasny\Session\MockSession;

$session = new MockSession([
  'foo.user' => 'john@example.com'
]);

$requestWithSession = $request->withAttribute('session', $session);
$response = $router->handle($requestWithSession);
```

Alternatively you can pass a session object when creating the `SessionMiddleware`. This session object will be used
instead of the global session.

```php
use Jasny\Session\SessionMiddleware;
use Jasny\Session\MockSession;

$mockSession = new MockSession();

$router->add(new SessionMiddleware($mockSession));
$response = $router->handle($request);
``` 
