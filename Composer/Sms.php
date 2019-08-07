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
    
    private $recipients = array();
    private $from = null;
    private $content = null;

    /**
     * Add recipient to a list of recipients the sms will be sent to
     *
     * @param String $recipient
     * @return void
     */ 
    public function addRecipient($recipient)
    {
        if (!in_array($recipient, $this->recipients)) {
            $this->recipients[] = $recipient;
        }
    }

    /**
     * Sets an array of recipients the sms will be sent to
     *
     * @param Array
     * @return void
     */ 
    public function setRecipients($recipients)
    {
        return $this->recipients = array_unique($recipients);
    }

    /**
     * Retrieves an array of recipient's recipients
     *
     * @return Array
     */ 
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Clears recipients list
     *
     * @param void
     * @return void
     */ 
    public function clearRecipients()
    {
        $this->recipients = array();
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
