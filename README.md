# DockerSecretsProvider

`DockerSecretsProvider` is a [Pimple] [ServiceProvider] to manage sensitive data with [Docker secrets],
mostly useful for [Silex] based applications.

Docker 1.13 provides secrets in swarm mode.

You can use secrets to manage any sensitive data which a Docker container needs at runtime
but you don't want to store in the image or in source control, such as:
- Usernames and passwords
- TLS certificates and keys
- SSH keys
- Other important data such as the name of a database or internal server
- Generic strings or binary content (up to 500 kb in size)

[Pimple]: https://pimple.symfony.com/
[ServiceProvider]: https://pimple.symfony.com/#extending-a-container
[Silex]: https://silex.symfony.com/
[Docker secrets]:  https://docs.docker.com/engine/swarm/secrets/

## Usage

Create the secret, using `docker` CLI

```sh
echo -n "This is a secret" | docker secret create my_secret_data -
```

Note the `-n` parameter with echo; this is necessary to suppress the trailing newline character. If you don't do this, your value is not correctly encoded.

Mount it into container, this example is for `docker-compose` or `docker stack deploy`

```yml
version: "3.1"

services:
  app:
  ...
    secrets:
      - my_secret_data
...
secrets:
  my_secret_data:
    external: true

```

Require the library
```sh
composer require glen/docker-secrets-provider
```

Map the secret in Pimple container

```php
$app->register(new DockerSecretsProvider(array(
   'my_secret_data' => 'my.secret',
)));
```

This would make `$app['my.secret']` read as `"This is a secret"`

In case of nested structure, the value can be callback,
this allows to assign value to array sub-keys.

However, in case the parent array is also lazy inited,
you need to make copy and assign the value again.

Here's example using [saxulum/saxulum-doctrine-mongodb-odm-provider]:

```php
$this->register(new DockerSecretsProvider([
    'mongodb' => function ($secretReader, $app) {
        $options = $app['mongodb.options'];

        $app['mongodb.options'] = function () use ($secretReader, $options, $app) {
            $options['options']['password'] = $secretReader();

            return $options;
        };
    },
]));
```

[saxulum/saxulum-doctrine-mongodb-odm-provider]: https://packagist.org/packages/saxulum/saxulum-doctrine-mongodb-odm-provider