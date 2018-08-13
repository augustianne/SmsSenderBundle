<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Senders;

use \Exception;

use Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException;
use Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor;

/**
 * Actual sending of sms
 *
 * @author  Yan Barreta
 * @version dated: Aug 9, 2018
 */
class SmsSender
{
    protected $config;
    protected $smsGateways;

    private $smsGateway;
    
    public function __construct(ConfigurationAccessor $config, $smsGateways)
    {
        foreach ($smsGateways as $smsGateway) {
            $this->smsGateways[$smsSender->getName()] = $smsGateway;
        }

        $this->smsGateway = $this->smsGateways[$this->config->getDefaultGatewayId()];
    }    

    /**
     * Send sms using the set gateway
     *
     * @param Sms $sms
     * @return void
     * @throws Exception
     */ 
    public function send(Sms $sms)
    {
        if (!$this->config->isDeliveryEnabled()) {
            return;
        }

        try {
            return $this->smsGateway->send($sms);
        }
        catch (DeliveryFailureException $e) {
            if (!is_null($this->config->getBackupGatewayId())) {
                $backupGateway = $this->config->getGatewayConfigurationByGatewayId($this->config->getBackupGatewayId());

                return $backupGateway->send($sms);
            }

            throw $e;    
        }
    }
}
