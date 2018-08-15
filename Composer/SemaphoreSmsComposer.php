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
class SemaphoreSmsComposer extends SmsComposer
{

    /**
     * Make the recipients ready for sending according to gateway rules
     *
     * @param Sms $sms
     * @return void
     */ 
    public function internationalizeNumbers(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        return;
    }

    /**
     * Compose recipients according to gateway rules
     *
     * @param Sms $sms
     * @return void
     */ 
    public function formatRecipientsForSending(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        $recipients = $sms->getRecipients();

        return implode(',', $recipients);
    }
}
