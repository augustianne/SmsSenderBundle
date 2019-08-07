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
use Yan\Bundle\SmsSenderBundle\Gateway\EngageSparkSmsGateway;

/**
 * Actual sending of sms using engage spark send sms to phone number api
 *
 * @author  Yan Barreta
 * @version dated: Aug 9, 2018
 */
class EngageSparkPhoneNumberSmsGateway extends EngageSparkSmsGateway
{
    protected $url = 'https://api.engagespark.com/v1/sms/phonenumber';
    protected $creditUrl = 'https://start.engagespark.com/api/v1/organizations/%s/available-balance';
    protected $name = 'ENGAGE_SPARK_PHONE_NUMBER';

    /**
     * Sends actual sms
     *
     * @param Sms $sms
     * @return void
     * @throws Exception
     */ 
    public function send(Sms $sms)
    {
        if (!$this->config->isDeliveryEnabled()) {
            return;
        }

        $gatewayConfiguration = $this->getGatewayConfiguration();
        $smses = $this->smsComposer->compose($sms, $gatewayConfiguration, true);

        foreach ($smses as $iSms) {
            $result = $this->curl->post(
                $this->getUrl(), 
                json_encode($this->smsComposer->composeSmsParameters($iSms, $gatewayConfiguration)),
                array(
                    sprintf('Authorization: Token %s', $gatewayConfiguration->getApiKey()),
                    'Content-type: application/json'
                )
            );

            try {
                $this->handleResult($result);
            } catch(DeliveryFailureException $e) {
                throw $e;
            }
            
        }

        return true;
    }

    /**
     * Handles results
     *
     * @param $result
     * @throws DeliveryFailureException
     */ 
    public function handleResult($result)
    {   
        if (empty($result)) {
            throw new DeliveryFailureException('Request sending failed: Empty response');
        }

        $json = json_decode($result, true);

        if (!is_array($json)) {
            throw new DeliveryFailureException('Request sending failed.', $json);
        }

        if (!isset($json['messageId'])) {
            throw new DeliveryFailureException('Request sending failed: messageId not returned', $json);    
        }

        if (isset($json['error']) && !empty($json['error'])) {
            throw new DeliveryFailureException($json['error'], $json);
        }
    }
}
