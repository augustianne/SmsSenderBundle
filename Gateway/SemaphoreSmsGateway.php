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
use Yan\Bundle\SmsSenderBundle\Gateway\SmsGateway;

/**
 * Actual sending of sms
 *
 * @author  Yan Barreta
 * @version dated: Aug 9, 2018
 */
class SemaphoreSmsGateway extends SmsGateway
{
    protected $url;
    protected $name;

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
