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

        $recipients = $sms->getRecipients();

        if (empty($recipients)) {
            return "[]";
        }

        return sprintf('["%s"]', implode('","', $recipients));
    }

    /**
     * Compose recipients according to gateway rules
     *
     * @param Sms $sms
     * @return void
     */ 
    public function formatRecipientsForDisplay(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        $this->internationalizeNumbers($sms, $gatewayConfiguration);
        $recipients = $sms->getRecipients();

        return implode(',', $recipients);
    }
}
