<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Sender;

use \Exception;

use Yan\Bundle\SmsSenderBundle\Composer\Sms;
use Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException;
use Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException;
use Yan\Bundle\SmsSenderBundle\Gateway\SmsGatewayProvider;
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
    protected $smsGatewayProvider;
    
    public function __construct(ConfigurationAccessor $config, SmsGatewayProvider $smsGatewayProvider)
    {
        $this->config = $config;
        $this->smsGatewayProvider = $smsGatewayProvider;
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
            $defaultSmsGateway = $this->smsGatewayProvider->getDefaultGateway();
            return $defaultSmsGateway->send($sms);
        }
        catch (GatewayNotFoundException $e) {
            return $this->sendViaBackupGateway($sms);
        }
        catch (DeliveryFailureException $e) {
            return $this->sendViaBackupGateway($sms);
        }
    }

    public function sendViaBackupGateway($sms)
    {
        try {
            $gateway = $this->smsGatewayProvider->getBackupGateway();
            return $gateway->send($sms);
        }
        catch (GatewayNotFoundException $e) {
            throw new DeliveryFailureException();
        }
        catch (DeliveryFailureException $e) {
            throw $e;
        }
    }
}
