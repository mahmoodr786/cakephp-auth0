# CakePHP Auth0 Adaptor 


### Installation

```sh
composer require mahmoodr786/cakephp-auth0
```

Load the adaptor in your AppController. Add the code below to your initialize function.

```php
$this->loadComponent('Auth', [
        'storage' => 'Memory',
        'authenticate' => [
                'Form' => [
                        'scope' => ['Users.status' => 1],
                ],
                'Mahmoodr786/AuthZero.Auth0' => [
                        'userModel' => 'Users',
                        'scope' => ['Users.status' => 1],
                        'client_id' => 'CLIENT ID HERE',
                        'secret' => 'SECRET HERE',
                        'fields' => [
                                'username' => 'id',
                        ],
                ],
        ],
        'checkAuthIn' => 'Controller.initialize',
]);
```

That is it. Send a request to your app with the Authorization Bearer YourAuth0JWTToken header and you should have access to the user in your app.
```php
debug($this->Auth->user());
```
### Google and Facebook
To use Google and Facebook loging set it up using Auth0 add rule to Auth0 and sync the user's credentials in database. Add column to your user table in database called external_id and store the facebook|12323445 or google-oauth2|1234567 id in that field as you get it from Auth0. That should do it.