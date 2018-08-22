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
        $smses = $this->smsComposer->compose($sms, $gatewayConfiguration);

        foreach ($smses as $iSms) {
            $result = $this->curl->post(
                $this->getUrl(), 
                $this->smsComposer->composeSmsParameters($iSms, $gatewayConfiguration),
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
     * Retrieves sms credits left on account
     *
     * @throws DeliveryFailureException
     * @return int
     */ 
    public function getAccountBalance()
    {
        $gatewayConfiguration = $this->getGatewayConfiguration();

        $result = $this->curl->get(
            $this->getCreditUrl(),
            array('apikey' => $gatewayConfiguration->getApiKey())
        );
        
        $json = json_decode($result, true);
        
        if (!is_array($json)) {
            throw new DeliveryFailureException('Request sending failed.');
        }

        if (!isset($json['credit_balance'])) {
            throw new DeliveryFailureException('Request sending failed.');
        }

        return $json['credit_balance'];
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

        if (isset($json['detail']) && $json['detail'] == 'Authentication credentials were not provided.') {
            throw new DeliveryFailureException($json['detail'], $json);
        }

        if (isset($json['error'])) {
            throw new DeliveryFailureException($json['error'], $json);
        }
    }
}
