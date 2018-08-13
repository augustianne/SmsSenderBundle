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
use Yan\Bundle\SmsSenderBundle\Tools\Accessor\ConfigurationAccessor;
use Yan\Bundle\SmsSenderBundle\Tools\GatewayConfiguration;

/**
 * Compose sms sms parameters
 *
 * @author  Yan Barreta
 * @version dated: August 8, 2018
 */
class SmsComposer
{

    const CHARACTER_LIMIT = 155;
    
    protected $config;

    public function __construct(ConfigurationAccessor $config)
    {
        $this->config = $config;
    }

    /**
     * Accepts a Sms object, splits it into Smss 
     * that has content under 155 characters
     * 
     * @param Sms
     * @return Array of Smss
     */ 
    public function compose(Sms $sms, GatewayConfiguration $gatewayConfiguration)
    {
        $testDeliveryNumbers = $gatewayConfiguration->getTestDeliveryNumbers();

        if (!empty($testDeliveryNumbers)) {
            $formattedSms = sprintf('Sent to: %s. %s', $sms->formatNumber(), $sms->getContent());

            $sms->setContent($formattedSms);
            $sms->setNumbers($testDeliveryNumbers);
        }

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
        $smss = array();

        foreach ($newSms as $key => $iNewSms) {
            if ($parts > 1) {
                $part = ($key+1);
                array_unshift($iNewSms, "$part/$parts");
            }

            $clonedSms = clone ($sms);
            $clonedSms->setContent(implode(' ', $iNewSms));
            
            $smss[] = $clonedSms;
        }

        return array_reverse($smss);
    }

}
