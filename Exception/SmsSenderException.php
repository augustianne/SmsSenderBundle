<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Exception;

use \Exception;

/**
 * Exception for when an sms sender is not defined
 *
 * @author  Yan Barreta
 * @version dated: August 9, 2018
 */

class SmsSenderException extends Exception
{
	public function __construct($message="Sms sender exception")
	{
		parent::__construct($message);
	}
}
