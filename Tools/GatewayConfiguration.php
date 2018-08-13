<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Tools;

use Yan\Bundle\SmsSenderBundle\Exception\GatewayNotFoundException;

/**
* Class for bundle configuration
*
* @author  Yan Barreta
* @version dated: August 9, 2018
*/
class GatewayConfiguration
{
    private $parameters;

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getApiName()
    {
        return $this->parameters['api_name'];
    }

    public function getApiKey()
    {
        return $this->parameters['api_key'];
    }

    public function getSenderName()
    {
        return $this->parameters['sender_name'];
    }

    public function isTruncateSms()
    {
        return $this->parameters['truncate_sms'];
    }

    public function getTestDeliveryNumbers()
    {
        return $this->parameters['test_delivery_numbers'];
    }

    public function getOrganizationId()
    {
        return $this->parameters['organization_id'];
    }

    public function getRecipientType()
    {
        return $this->parameters['recipient_type'];
    }
}