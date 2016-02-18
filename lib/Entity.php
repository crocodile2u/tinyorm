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


namespace tinyorm;


abstract class Entity
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var string
     */
    protected $sourceName;
    /**
     * @var string
     */
    protected $pkName = "id";
    /**
     * These fields will not be inserted/updated, the DB backend is responsible for them.
     *
     * @var string[]
     */
    protected $autoUpdatedCols = [];

    /**
     * Entity constructor.
     * @param array $data
     */
    function __construct(array $data = [])
    {
        $this->data = array_merge($this->getDefaults(), $data);
    }

    /**
     * @return string
     */
    function getSourceName()
    {
        return $this->sourceName;
    }

    /**
     * @return string
     */
    function getPKName()
    {
        return $this->pkName;
    }

    /**
     * @return string
     */
    function getPK()
    {
        return $this->{$this->getPKName()};
    }

    /**
     * @return string
     */
    function setPK($value)
    {
        $this->__set($this->getPKName(), $value);
    }

    /**
     * @return string[]
     */
    function getAutoUpdatedCols($flipped = false)
    {
        return $flipped ? array_flip($this->autoUpdatedCols) : $this->autoUpdatedCols;
    }

    /**
     * @return array
     */
    abstract function getDefaults();

    /**
     * @return Mapper
     */
    function getMapper()
    {
        return new Mapper($this);
    }

    function toArray()
    {
        return array_intersect_key($this->data, $this->getDefaults());
    }

    function __get($name)
    {
        return $this->data[$name];
    }

    function __set($name, $value)
    {
        return $this->data[$name] = $value;
    }
}