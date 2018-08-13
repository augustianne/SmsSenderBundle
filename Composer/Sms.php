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

/**
 * SMS Message properties
 *
 * @author  Yan Barreta
 * @version dated: August 8, 2018
 */
class Sms
{
    
    private $numbers = array();
    private $from = null;
    private $content = null;

    /**
     * Add number to a list of numbers the sms will be sent to
     *
     * @param String $number
     * @return void
     */ 
    public function addNumber($number)
    {
        if (!in_array($number, $this->numbers)) {
            $this->numbers[] = $number;
        }
    }

    /**
     * Sets an array of numbers the sms will be sent to
     *
     * @return void
     */ 
    public function setNumbers($numbers)
    {
        return $this->numbers = array_unique($numbers);
    }

    /**
     * Retrieves an array of recipeint's numbers
     *
     * @return Array
     */ 
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * Format the array of numbers to be ready for sending
     *
     * @return Array
     */ 
    public function formatNumber()
    {
        return implode(',', $this->numbers);
    }

    /**
     * Sets the sender of the sms
     *
     * @return String
     */ 
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Retrieves sender of the sms
     *
     * @return String
     */ 
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets the message of the sms
     *
     * @return String
     */ 
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Retrieves message of the sms
     *
     * @return String
     */ 
    public function getContent()
    {
        return $this->content;
    }

}
