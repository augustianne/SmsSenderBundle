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

use Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException;
use Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor;

/**
 * Actual sending of sms
 *
 * @author  Yan Barreta
 * @version dated: Aug 9, 2018
 */
class SmsGatewayProvider
{
    private $config;
    private $smsGateways;

    public function __construct(ConfigurationAccessor $config, $smsGateways)
    {
        $this->config = $config;

        

        foreach ($smsGateways as $smsGateway) {
            $gatewayId = $this->config->getGatewayIdByApiName($smsGateway->getName());

            if (!is_null($gatewayId)) {
                $this->smsGateways[$gatewayId] = $smsGateway;
            }
        }
    }

    public function getDefaultGateway()
    {
        return $this->getGatewayById($this->config->getDefaultGatewayId());
    }

    public function getBackupGateway()
    {
        return $this->getGatewayById($this->config->getBackupGatewayId());
    }

    public function getGatewayById($gatewayId)
    {
        if (!isset($this->smsGateways[$gatewayId])) {
            throw new GatewayNotFoundException();
        }

        return isset($this->smsGateways[$gatewayId])
            ? $this->smsGateways[$gatewayId] : null;   
    }
}
