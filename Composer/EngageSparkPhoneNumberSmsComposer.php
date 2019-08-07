<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Composer;

use Yan\Bundle\SmsSenderBundle\Composer\Sms;
use Yan\Bundle\SmsSenderBundle\Composer\EngageSparkSmsComposer;
use Yan\Bundle\SmsSenderBundle\Exception\InvalidGatewayParameterException;
use Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor;
use Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration;

/**
 * Compose sms sms parameters
 *
 * @author  Yan Barreta
 * @version dated: August 13, 2018
 */
class EngageSparkPhoneNumberSmsComposer extends EngageSparkSmsComposer
{
    protected $requiredParameters = array(
        'orgId', 'to', 'from', 'message'
    );

    /**
     * Composes text message for sending
     *
     * @param Message $message
     * @return Array
     */ 
    public function composeSmsParameters(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        $formattedMessage = $sms->getContent();
        
        $params = array(
            'orgId' => $gatewayConfiguration->getOrganizationId(),
            'to' => implode("", $sms->getRecipients()),
            'from' => $gatewayConfiguration->getSenderName(),
            'message' => $formattedMessage
        );
        
        $this->validateRequiredParameters($params);

        return $params;
    }
}
