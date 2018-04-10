<?php

namespace glen\DockerSecretsProvider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Service Provider for docker secrets.
 *
 *  echo -n "This is a secret" | docker secret create my_secret_data -
 *
 *  $app->register(new DockerSecretsProvider([
 *      'my_secret_data' => 'my.secret',
 *  ]));
 *
 * This would make $app['my.secret'] read as "This is a secret"
 *
 * @see https://docs.docker.com/engine/swarm/secrets/
 */
class DockerSecretsProvider implements ServiceProviderInterface {
	const SECRETS_PATH = '/run/secrets';

	/**
	 * Mapping for docker secret to Container key.
	 *
	 * @var string[]
	 */
	private $secrets;

	public function __construct(array $secrets = array()) {
		$this->secrets = $secrets;
	}

	/**
	 * @inheritdoc
	 */
	public function register(Container $app) {
		foreach ($this->secrets as $secretName => $value) {
			$fileName = self::SECRETS_PATH . '/' . $secretName;

			if (!file_exists($fileName)) {
				continue;
			}

			$secretReader = function () use ($fileName) {
				// allow IO errors to get NULL or FALSE return values
				return file_get_contents($fileName);
			};

			// let closure figure out what to do with the value
			if (method_exists($value, '__invoke')) {
				$value($secretReader, $app);

				continue;
			}

			$app[$value] = $secretReader;
		}
	}
}
