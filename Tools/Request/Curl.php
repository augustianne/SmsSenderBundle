<?php

/*
 * This file is part of SmsSenderBundle.
 *
 * Yan Barreta <augustianne.barreta@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yan\Bundle\SmsSenderBundle\Tools\Request;

use \Exception;

use Yan\Bundle\SmsSenderBundle\Tools\Request\CurlRequest;

/**
 * cUrl wrapper
 *
 * @author  Yan Barreta
 * @version dated: August 7, 2018
 */
class Curl
{

    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new Exception('Curl is not enabled.');
        }
    }

    public function post($url, $parameters=array(), $headers=array())
    {
        $curlRequest = new CurlRequest($url);
        $curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        // $curlRequest->setOption(CURLOPT_HEADER, 0);
        $curlRequest->setOption(CURLOPT_HTTPHEADER, $headers);
        $curlRequest->setOption(CURLOPT_VERBOSE, 0);
        $curlRequest->setOption(CURLOPT_POST, true);
        $curlRequest->setOption(CURLOPT_POSTFIELDS, $parameters);
        
        $result = $curlRequest->execute();
        
        $curlRequest->close();

        return $result;
    }

    public function get($url, $parameters = array())
    {
        $formattedUrl = sprintf("%s?%s", $url, http_build_query($parameters));

        $curlRequest = new CurlRequest($url);
        $curlRequest->setOption(CURLOPT_URL, $formattedUrl);
        $curlRequest->setOption(CURLOPT_FOLLOWLOCATION, true);
        $curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        
        $result = $curlRequest->execute();
        
        $curlRequest->close();

        return $result;
    }
}
