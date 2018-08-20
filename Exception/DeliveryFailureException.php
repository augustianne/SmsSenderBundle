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
 * Exception for when sms delivery fails
 *
 * @author  Yan Barreta
 * @version dated: August 9, 2018
 */

class DeliveryFailureException extends Exception
{

	private $json = null;

	public function __construct($message="Request sending failed", $json=array())
	{
		$this->json = $json;
		parent::__construct($message);
	}

	public function getJsonResult()
	{
		return $this->json;
	}

}
