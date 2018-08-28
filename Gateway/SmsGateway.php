<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Gateway;

use \Exception;

use Yan\Bundle\SmsSenderBundle\Composer\Sms;
use Yan\Bundle\SmsSenderBundle\Composer\SmsComposer;
use Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException;
use Yan\Bundle\SmsSenderBundle\Tools\Request\Curl;
use Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor;

/**
 * Actual sending of sms
 *
 * @author  Yan Barreta
 * @version dated: Aug 9, 2018
 */
abstract class SmsGateway
{
    protected $name;
    protected $url;
    protected $creditUrl;
    protected $config;
    protected $curl;
    protected $smsComposer;
    
    public function __construct(ConfigurationAccessor $config, Curl $curl, SmsComposer $smsComposer)
    {
        $this->config = $config;
        $this->curl = $curl;
        $this->smsComposer = $smsComposer;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getCreditUrl()
    {
        return $this->creditUrl;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getGatewayConfiguration()
    {
        return $this->config->getGatewayConfigurationByApiName($this->getName());
    }

    abstract public function send(Sms $sms);
    abstract public function getAccountBalance();
    abstract public function handleResult($result);
}
