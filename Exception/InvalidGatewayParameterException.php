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

use Yan\Bundle\SmsSenderBundle\Exception\SmsSenderException;

/**
 * Exception for when an sms sender is not defined
 *
 * @author  Yan Barreta
 * @version dated: August 13, 2018
 */

class InvalidGatewayParameterException extends SmsSenderException
{

	public function __construct($message="Invalid gateway")
	{
		parent::__construct($message);
	}
}
