<?php

namespace Yan\Bundle\SmsSenderBundle\Tests\Integration\Services;

use \DirectoryIterator;
use \PHPUnit_Framework_AssertionFailedError;

use \Symfony\Component\Yaml\Yaml;

class SmsSenderServiceExistenceTest extends \PHPUnit_Framework_TestCase
{
    private $ignoreList = array(
        'DependencyInjection',
        'Exception',
        'Resources',
        'Tests',
        'vendor',
        'composer.json',
        'composer.lock',
        'CurlRequest.php', 
        'GatewayConfiguration.php',
        'LICENSE',
        'phpunit.xml.dist',
        'README.md',
        'Sms.php',
        '.DS_Store',
        '.git',
        '.gitignore'
    );

    public function testServicesYml()
    {
        $this->checkExistenceOfFileInServicesYml(dirname(__DIR__.'/../../../../'));
    }

    private function checkExistenceOfFileInServicesYml($dirPath)
    {
        $serviceClasses = $this->getServiceClasses();

        $directory = new DirectoryIterator($dirPath);
        foreach ($directory as $iFile) {
            if ($iFile->isDir() && !$iFile->isDot() && !in_array($iFile->getFileName(), $this->ignoreList)) {
                $this->checkExistenceOfFileInServicesYml($iFile->getPathName());
            }

            else if ($iFile->isFile() && !in_array($iFile->getFileName(), $this->ignoreList)) {
                $file = str_replace('.php', '', $iFile->getFileName());
                $this->assertTrue(in_array($file, $serviceClasses), sprintf('%s.php is not defined in services.yml', $file));
            }
        }
    }

    private function getServiceClasses()
    {
        $services = Yaml::parse(__DIR__.'/../../../Resources/config/services.yml');

        $serviceClasses = array();
        foreach ($services['services'] as $service) {
            $pathParts = explode('\\', $service['class']);
            $className = end($pathParts);
            $serviceClasses[] = $className;
        }

        return $serviceClasses;
    }

}
