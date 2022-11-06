<?php

namespace Atournayre\Bundle\ConfirmationBundle\Config;

use Atournayre\Bundle\ConfirmationBundle\Exception\ConfirmationCodeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

class LoaderConfig
{
    private array $config;

    public function __construct()
    {
        $configFile = \dirname(__DIR__) . '/../config/packages/atournayre_confirmation.yaml';
//        $configFile = \dirname(__DIR__) . '/../../../config/packages/atournayre_confirmation.yaml';
        if (!file_exists($configFile)) {
            throw new \LogicException(sprintf('The file "%s" is missing.', $configFile));
        }

        $config = Yaml::parseFile($configFile);
        $this->config = $config['atournayre_confirmation'] ?? [];
    }

    public function getProviders(): array
    {
        return $this->config['providers'] ?? [];
    }

    /**
     * @throws ConfirmationCodeException
     */
    public function getProvider(string $mappingType): string
    {
        $mappings = $this->getProviders();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $providerClass = $propertyAccessor->getValue($mappings, sprintf('[%s]', $mappingType));

        if (is_null($providerClass)) {
            throw ConfirmationCodeException::entityClassNotDefined($mappingType);
        }

        if (!class_exists($providerClass)) {
            throw ConfirmationCodeException::classNotFound($providerClass);
        }

        return $providerClass;
    }
}
