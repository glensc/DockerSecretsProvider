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

1. create secret, mount it into container
2. load secret in pimple container

 ```sh
 echo "This is a secret" | docker secret create my_secret_data -
 ```
 
 ```php
   $app->register(new DockerSecretsProvider(array(
       'my_secret' => 'my.secret',
   )));
 ```
 
 This would make `$app['my.secret']` read as `"This is a secret"`
 
