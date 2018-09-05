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
    protected $usedGateway;
    
    public function __construct(ConfigurationAccessor $config, SmsGatewayProvider $smsGatewayProvider)
    {
        $this->config = $config;
        $this->smsGatewayProvider = $smsGatewayProvider;
    }

    /**
     * Send sms using the default gateway
     *
     * @param Sms $sms
     * @return void
     * @throws GatewayNotFoundException
     * @throws DeliveryFailureException
     */ 
    public function send(Sms $sms)
    {
        if (!$this->config->isDeliveryEnabled()) {
            return;
        }

        $clonedSms = clone($sms);

        try {
            $defaultSmsGateway = $this->smsGatewayProvider->getDefaultGateway();
            $this->usedGateway = $defaultSmsGateway;
            return $defaultSmsGateway->send($sms);
        }
        catch (GatewayNotFoundException $e) {
            return $this->sendViaBackupGateway($clonedSms);
        }
        catch (DeliveryFailureException $e) {
            return $this->sendViaBackupGateway($clonedSms);
        }
    }

    /**
     * Send sms using the backup gateway
     *
     * @param Sms $sms
     * @return void
     * @throws GatewayNotFoundException
     * @throws DeliveryFailureException
     */
    public function sendViaBackupGateway(Sms $sms)
    {
        try {
            $gateway = $this->smsGatewayProvider->getBackupGateway();
            $this->usedGateway = $gateway;
            return $gateway->send($sms);
        }
        catch (GatewayNotFoundException $e) {
            throw new DeliveryFailureException();
        }
        catch (DeliveryFailureException $e) {
            throw $e;
        }
    }

    /**
     * Retrieves balance of default gateway
     *
     * @return void
     * @throws GatewayNotFoundException
     * @throws DeliveryFailureException
     */
    public function getAccountBalance()
    {
        try {
            $defaultSmsGateway = $this->smsGatewayProvider->getDefaultGateway();
            $this->usedGateway = $defaultSmsGateway;
            return $defaultSmsGateway->getAccountBalance();
        }
        catch (GatewayNotFoundException $e) {
            return $this->getBackupAccountBalance();
        }
        catch (DeliveryFailureException $e) {
            return $this->getBackupAccountBalance();
        }
    }

    public function getBackupAccountBalance()
    {
        try {
            $gateway = $this->smsGatewayProvider->getBackupGateway();
            $this->usedGateway = $gateway;
            return $gateway->getAccountBalance();
        }
        catch (GatewayNotFoundException $e) {
            throw new DeliveryFailureException();
        }
        catch (DeliveryFailureException $e) {
            throw $e;
        }
    }

    public function getUsedGateway()
    {
        return $this->usedGateway;
    }
}
