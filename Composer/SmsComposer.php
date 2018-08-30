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
use Yan\Bundle\SmsSenderBundle\Exception\InvalidGatewayParameterException;
use Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor;
use Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration;

/**
 * Compose sms sms parameters
 *
 * @author  Yan Barreta
 * @version dated: August 8, 2018
 */
abstract class SmsComposer
{

    const CHARACTER_LIMIT = 155;
    
    protected $config;

    protected $validParameters = array();
    protected $requiredParameters = array();

    public function __construct(ConfigurationAccessor $config)
    {
        $this->config = $config;
    }

    abstract public function composeSmsParameters(Sms $sms, GatewayConfiguration $gatewayConfiguration);
    abstract public function internationalizeNumbers(Sms $sms, GatewayConfiguration $gatewayConfiguration);

    /**
     * Accepts a Sms object, splits it into Smss 
     * that has content under 155 characters
     * 
     * @param Sms
     * @return Array of Smses
     */ 
    public function compose(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        $testDeliveryNumbers = $gatewayConfiguration->getTestDeliveryNumbers();

        if (!empty($testDeliveryNumbers)) {
            $formattedSms = sprintf('Recipients: %s. Gateway: %s. Message: %s', $this->formatRecipientsForSending($sms, $gatewayConfiguration), $gatewayConfiguration->getApiName(), $sms->getContent());

            $sms->setContent($formattedSms);
            $sms->setRecipients($testDeliveryNumbers);
        }

        $this->internationalizeNumbers($sms, $gatewayConfiguration);

        $smses = array($sms);

        if (!$gatewayConfiguration->isTruncateSms()) {
            return $smses;    
        }    
        
        return $this->constructSmses($sms);
    }

    /**
     * Splits string into 155-character substrings
     *
     * @param string
     * @return Array
     */ 
    public function splitSms($string) 
    {
        $words = explode(' ', $string);
        
        $newSms = array();
        $temp = array();
        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];

            $temp[] = $word;
            $tempString = implode(' ', $temp);
            $tempStringLength = strlen($tempString);
            
            if ($tempStringLength > self::CHARACTER_LIMIT) {
                $theword = array_pop($temp);
                $newSms[] = $temp;
                
                if (strlen($theword) <= self::CHARACTER_LIMIT) {
                    $temp = array();
                    $i--;
                }
                else {
                    $temp = array($theword);
                }
            }
        }

        $newSms[] = $temp;

        return $newSms;
    }

    /**
     * Accepts a Sms object, splits it into Smss 
     * that has content under 155 characters
     * 
     * @param Sms
     * @return Array of Smss
     */ 
    public function constructSmses(Sms $sms) 
    {   
        $content = $sms->getContent();
        $newSms = $this->splitSms($content);

        $parts = count($newSms);
        $smses = array();

        foreach ($newSms as $key => $iNewSms) {
            if ($parts > 1) {
                $part = ($key+1);
                array_unshift($iNewSms, "$part/$parts");
            }

            $clonedSms = clone ($sms);
            $clonedSms->setContent(implode(' ', $iNewSms));
            
            $smses[] = $clonedSms;
        }

        return array_reverse($smses);
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
        $invalid = array();

        $diff = array_diff($this->requiredParameters, array_keys($parameters));
        if (!empty($diff)) {
            throw new InvalidGatewayParameterException(sprintf(
                "The following parameters are required and should be present: [%s]",
                implode(', ', $diff)
            ));
        }

        foreach ($parameters as $key => $parameter) {
            if (in_array($key, $this->requiredParameters) && empty($parameter)) {
                $invalid[] = $key;
            }
        }

        if (!empty($invalid)) {
            throw new InvalidGatewayParameterException(sprintf(
                "The following parameters are required and should not be left blank: [%s]",
                implode(', ', $invalid)
            ));
        }
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
