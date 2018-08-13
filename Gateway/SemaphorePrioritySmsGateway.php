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

use Yan\Bundle\SmsSenderBundle\Senders\SemaphoreSmsGateway;

/**
 * Actual sending of sms
 *
 * @author  Yan Barreta
 * @version dated: Aug 9, 2018
 */
class SemaphorePrioritySmsGateway extends SemaphoreSmsGateway
{
    protected $url = 'http://api.semaphore.co/api/v4/priority';
    protected $name = 'SEMAPHORE_PRIORITY';
}
