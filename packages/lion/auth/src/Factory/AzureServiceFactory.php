<?php
namespace Lion\Auth\Factory;

use Lion\Auth\Dto\UserData;
use Lion\Auth\Service\AzureService;
use Lion\Auth\Service\Graph;
use Lion\Auth\Service\OAuth;
use Lion\Auth\Service\Session;
use Symfony\Component\Yaml\Yaml;

class AzureServiceFactory
{
    /**
     * @return AzureService
     */
    public function create($host, $rootPath, $pathToConfig): AzureService
    {
        $config = Yaml::parseFile($rootPath.$pathToConfig.'lion-auth-config.yaml');
        $config['urls']['base_url'] = $host;
        return new AzureService(new OAuth($config), new Graph($config), new Session(), new UserData());
    }
}
