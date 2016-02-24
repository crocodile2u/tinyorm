<?php
/**
 * LogInterface.
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm\log;


interface LogInterface
{
    /**
     * @param $message
     * @return mixed
     */
    function write($message);
}