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

use Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException;
use Yan\Bundle\SmsSenderBundle\Senders\SmsGateway;

/**
 * Actual sending of sms
 *
 * @author  Yan Barreta
 * @version dated: Aug 9, 2018
 */
class EngageSparkSmsGateway extends SmsGateway
{
    protected $url = 'https://start.engagespark.com/api/v1/messages/sms';
    protected $name = 'ENGAGE_SPARK';

    /**
     * Composes text message for sending
     *
     * @param Message $message
     * @return Array
     */ 
    public function composeParameters(Sms $sms)
    {
        $gatewayConfiguration = $this->getGatewayConfiguration();

        $formattedNumbers = $sms->formatNumber();
        $formattedMessage = $sms->getContent();
        
        $params = array(
            'apikey' => $gatewayConfiguration->getApiKey(),
            'organization_id' => $gatewayConfiguration->getOrganizationId(),
            'recipient_type' => $gatewayConfiguration->getRecipientType()
            'mobile_numbers' => $formattedNumbers,
            'contact_ids' => $gatewayConfiguration->getContactId(),
            'message' => $formattedMessage,
            'sender_id' => $gatewayConfiguration->getSenderName()
        );

        return $params;
    }

    public function handleResult($result)
    {   
        $json = json_decode($result, true);
        
        if (!is_array($json)) {
            throw new DeliveryFailureException('Request sending failed.', $json);
        }
        
        if (!isset($json[0]['status']) || $json[0]['status'] === 'Failed') {
            throw new DeliveryFailureException('Request sending failed.', $json);   
        }
    }
}
