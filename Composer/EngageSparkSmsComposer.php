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
use Yan\Bundle\SmsSenderBundle\Composer\SmsComposer;
use Yan\Bundle\SmsSenderBundle\Exception\InvalidGatewayParameterException;
use Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor;
use Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration;

/**
 * Compose sms sms parameters
 *
 * @author  Yan Barreta
 * @version dated: August 13, 2018
 */
class EngageSparkSmsComposer extends SmsComposer
{
    protected $requiredParameters = array(
        'organization_id', 'recipient_type', 'sender_id', 'message'
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

        $sender = (!empty($sms->getFrom()) ? $sms->getFrom() : $gatewayConfiguration->getSenderName());
        
        $params = array(
            'organization_id' => $gatewayConfiguration->getOrganizationId(),
            'recipient_type' => $gatewayConfiguration->getRecipientType(),
            'sender_id' => $sender, 
            'message' => $formattedMessage
        );
        
        if (!in_array($params['recipient_type'], array('contact_id', 'mobile_number'))) {
            throw new InvalidGatewayParameterException(
                sprintf(
                    '%s is not included in the supported recipient types list. [mobile_number, contact_id]',
                    $params['recipient_type']
                )
            );
        }

        $recipientKey = 'mobile_numbers';
        if ($params['recipient_type'] == 'contact_id') {
            $recipientKey = 'contact_ids';
        }

        $params[$recipientKey] = $sms->getRecipients();

        $this->requiredParameters[] = $recipientKey;
        
        // $paramsForValidation = $params;
        // $paramsForValidation[$recipientKey] = $sms->getRecipients();
        $this->validateRequiredParameters($params);

        return $params;
    }

    /**
     * Make the recipients ready for sending according to gateway rules
     *
     * @param Sms $sms
     * @return void
     */ 
    public function internationalizeNumbers(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        $recipientType = $gatewayConfiguration->getRecipientType();
        if (!($recipientType && $recipientType == 'mobile_number')) {
            return;
        }

        $recipients = $sms->getRecipients();
        
        // $pattern = '~^
        //     (
        //         (\+|0)*
        //         (
        //             ([1-9]{1}[0-9]{1})
        //             ([1-9]{1}[0-9]{3,14})
        //         )
        //     )
        // $~ixu';
        $pattern = '~^
            (
                (0)*
                (
                    ([1-9]{1}[0-9]{1})
                    ([1-9]{1}[0-9]{3,14})
                )
            )
        $~ixu';

        $cleanRecipients = array();
        foreach ($recipients as $recipient) {

            $recipient = preg_replace("/[^\d]/", "", $recipient);
            preg_match($pattern, $recipient, $internationalMatches);
            
            if (isset($internationalMatches[3])) {
                if ($internationalMatches[2] == "0") {
                    $cleanRecipients[] = sprintf("%s%s", $gatewayConfiguration->getDefaultCountryCode(), $internationalMatches[3]);
                }
                else {
                    $cleanRecipients[] = $internationalMatches[3];
                }
                
            }
        }

        $sms->setRecipients($cleanRecipients);
    }

    /**
     * Compose recipients according to gateway rules
     *
     * @param Sms $sms
     * @return void
     */ 
    public function formatRecipientsForSending(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        $this->internationalizeNumbers($sms, $gatewayConfiguration);

        return parent::formatRecipientsForSending($sms, $gatewayConfiguration);
    }

    /**
     * Validate parameters based on gateway requirement
     *
     * @param Sms $sms
     * @return void
     * @throws InvalidGatewayParameterException
     */ 
    public function validateRequiredParameters($parameters)
    {
        if (isset($parameters['contact_ids']) && $parameters['contact_ids'] == '[]') {
            throw new InvalidGatewayParameterException(sprintf(
                "The following parameters are required and should not be left blank: [%s]",
                'contact_ids'
            ));
        }

        if (isset($parameters['mobile_numbers']) && $parameters['mobile_numbers'] == '[]') {
            throw new InvalidGatewayParameterException(sprintf(
                "The following parameters are required and should not be left blank: [%s]",
                'mobile_numbers'
            ));
        }

        parent::validateRequiredParameters($parameters);
    }
}
