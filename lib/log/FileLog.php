<?php
/**
 * Copyright (c) 2004-2015, EMESA BV
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are PROHIBITED without prior written permission from
 * the author. This product may NOT be used anywhere and on any computer
 * except the server platform of EMESA BV. If you received this code
 * accidentally and without intent to use it, please report this
 * incident to the author by email.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT
 * OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */
/**
 *
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm\log;


class FileLog implements LogInterface
{
    /**
     * @var resource
     */
    private $descriptor;
    /**
     * FileLog constructor.
     * @param string|resource $file
     */
    function __construct($file = STDOUT)
    {
        if (is_string($file)) {
            $this->descriptor = fopen($file, "w+");
        } elseif (is_resource($file)) {
            $this->descriptor = $file;
        } elseif ($file instanceof \SplFileObject) {
            $this->descriptor = $file->openFile("w+");
        } else {
            throw new \InvalidArgumentException("Argument 1 should a string, a file descriptor or an SplFileObject");
        }
    }

    /**
     * @param $message
     * @return int
     */
    function write($message)
    {
        return fwrite($this->descriptor, "[" . date("Y-m-d H:i:s") . "] " . $message . "\n");
    }
}