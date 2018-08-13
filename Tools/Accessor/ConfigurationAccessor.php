<?php

/*
 * This file is part of GatewayBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Tools\Accessor;

use Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException;
use Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration;

/**
* Class for bundle configuration
*
* @author  Yan Barreta
* @version dated: August 7, 2018
*/
class ConfigurationAccessor
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Retrieves enable_delivery
     *
     * @param void
     * @return String
     */
    public function isDeliveryEnabled()
    {
        return $this->container->getParameter('yan_sms_sender.enable_delivery');
    }

    /**
     * Retrieves default_gateway_id
     *
     * @param void
     * @return boolean
     */
    public function getDefaultGatewayId()
    {
        return $this->container->getParameter('yan_sms_sender.default_gateway_id');
    }

    /**
     * Retrieves backup_gateway_id
     *
     * @param void
     * @return boolean
     */
    public function getBackupGatewayId()
    {
        return $this->container->getParameter('yan_sms_sender.backup_gateway_id');
    }

    /**
     * Retrieves sms senders
     *
     * @param void
     * @return String
     */
    public function getGateways()
    {
        return $this->container->getParameter('yan_sms_sender.gateways');
    }

    /**
     * Retrieves sms sender configuration given the sms sender id
     *
     * @param void
     * @return String
     */
    public function getGatewayConfigurationByGatewayId($gatewayId)
    {
        $gateways = $this->getGateways();

        return new GatewayConfiguration($gateways[$gatewayId]);
    }

    /**
     * Retrieves default sms sender configuration
     *
     * @param void
     * @return String
     */
    public function getDefaultGatewayConfiguration()
    {
        $gateways = $this->getGateways();
        $gatewayId = $this->getDefaultGatewayId();

        return new GatewayConfiguration($gateways[$gatewayId]);
    }

    /**
     * Retrieves sms sender configuration given the api name
     *
     * @param String
     * @return String
     */
    public function getGatewayConfigurationByApiName($apiName)
    {
        $gateways = $this->getGateways();

        foreach ($gateways as $gatewayId => $gateway) {
            if (isset($gateway[$apiName])) {
                return $this->getGatewayConfigurationByGatewayId($gatewayId);
            }
        }

        return null;
    }
}