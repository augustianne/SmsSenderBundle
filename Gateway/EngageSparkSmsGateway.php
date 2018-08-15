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

use Yan\Bundle\SmsSenderBundle\Composer\Sms;
use Yan\Bundle\SmsSenderBundle\Exception\DeliveryFailureException;
use Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway;

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

        $formattedRecipients = $this->smsComposer->formatRecipientsForSending($sms);
        $formattedMessage = $sms->getContent();
        
        $params = array(
            'apikey' => $gatewayConfiguration->getApiKey(),
            'organization_id' => $gatewayConfiguration->getOrganizationId(),
            'recipient_type' => $gatewayConfiguration->getRecipientType(),
            'sender_id' => $gatewayConfiguration->getSenderName(),
            'message' => $formattedMessage
        );

        $recipientKey = 'mobile_numbers';
        if ($params['recipient_type'] == 'contact_id') {
            $recipientKey = 'contact_ids';
        }

        $params[$recipientKey] = sprintf("[%s]", $formattedRecipients);

        return $params;
    }

    /**
     * Handles results
     *
     * @param $result
     * @throws DeliveryFailureException
     */ 
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
