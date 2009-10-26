<?php
/**
 * This file is part of the static reflection component.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2009, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  PHP
 * @package   org\pdepend\reflection\api
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2009 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://pdepend.org/
 */

namespace org\pdepend\reflection\api;

/**
 * Static class implementation.
 *
 * @author  Manuel Pichler <mapi@pdepend.org>
 * @license Copyright by Manuel Pichler
 * @version $Revision$
 */
class StaticReflectionClass extends StaticReflectionInterface
{
    const TYPE = __CLASS__;

    /**
     * @var integer
     */
    private $_modifiers = 0;

    /**
     * @var \ReflectionClass
     */
    private $_parentClass = false;

    /**
     * @var array(\ReflectionProperty)
     */
    private $_properties = null;

    /**
     * @param string  $name
     * @param string  $docComment
     * @param integer $modifiers
     */
    public function __construct( $name, $docComment, $modifiers )
    {
        parent::__construct( $name, $docComment );

        $this->_modifiers = $modifiers;
    }

    public function getModifiers()
    {
        if ( count( $this->getMethods( StaticReflectionMethod::IS_ABSTRACT ) ) > 0 )
        {
            return $this->_modifiers | self::IS_IMPLICIT_ABSTRACT;
        }
        return $this->_modifiers;
    }

    /**
     * Returns <b>true</b> when the class is declared abstract or is an interface.
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return ( $this->_isExplicitAbstract() || $this->_isImplicitAbstract() );
    }

    /**
     * Returns <b>true</b> when the explicit abstract modifier is present.
     *
     * @return boolean
     */
    private function _isExplicitAbstract()
    {
        return ( ( $this->getModifiers() & self::IS_EXPLICIT_ABSTRACT ) === self::IS_EXPLICIT_ABSTRACT );
    }

    /**
     * Returns <b>true</b> when the implicit abstract modifier is present.
     *
     * @return boolean
     */
    private function _isImplicitAbstract()
    {
        return ( ( $this->getModifiers() & self::IS_IMPLICIT_ABSTRACT ) === self::IS_IMPLICIT_ABSTRACT );
    }

    /**
     * Returns <b>true</b> when the class is declared as final.
     *
     * @return boolean
     */
    public function isFinal()
    {
        return ( ( $this->_modifiers & self::IS_FINAL ) === self::IS_FINAL );
    }

    /**
     * Returns <b>true</b> when the reflected class/interface is an interface,
     * which means that this concrete implementation always returns <b>false</b>.
     * 
     * @return boolean
     */
    public function isInterface()
    {
        return false;
    }

    /**
     * Checks that the reflected class is a child of the given class name.
     *
     * @param string $class Name of the searched class.
     *
     * @return boolean
     */
    public function isSubclassOf( $class )
    {

    }

    /**
     * Returns the parent of the reflected class or <b>false</b> when no parent
     * exists.
     *
     * @return \ReflectionClass|boolean
     */
    public function getParentClass()
    {
        return $this->_parentClass;
    }

    /**
     * Initializes the parent class of the reflected class.
     *
     * @param \ReflectionClass $parentClass The parent class instance.
     *
     * @return void
     * @access private
     * @throws \LogicException When the parentClass property was already set.
     */
    public function initParentClass( \ReflectionClass $parentClass )
    {
        if ( $this->_parentClass === false )
        {
            $this->_parentClass = $parentClass;
        }
        else
        {
            throw new \LogicException( 'Property parentClass already set' );
        }
    }

    public function getConstants()
    {
        if ( $this->_parentClass === false )
        {
            return parent::getConstants();
        }
        return $this->_collectConstants( $this->_parentClass, parent::getConstants() );
    }

    private function _collectConstants( \ReflectionClass $class, array $result )
    {
        foreach ( $class->getConstants() as $name => $value )
        {
            if ( array_key_exists( $name, $result ) === false )
            {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    /**
     * Returns an <b>array</b> with methods defined in the inheritence hierarchy
     * of the reflection class. You can pass an optional filter argument that
     * contains a bitfield of required method modifiers.
     *
     * @param integer $filter Optional filter for the returned methods
     *
     * @return array(\ReflectionMethod)
     */
    public function getMethods( $filter = -1 )
    {
        if ( $this->_parentClass === false )
        {
            return parent::getMethods( $filter );
        }
        return $this->_collectMethodsFromParentClass( $filter );
    }

    private function _collectMethodsFromParentClass( $filter )
    {
        $result = parent::collectMethods();
        foreach ( $this->_parentClass->getMethods() as $method )
        {
            $result = $this->_collectMethodFromParentClass( $method, $result );
        }
        return $this->prepareCollectedMethods( $filter, $result );
    }

    private function _collectMethodFromParentClass( \ReflectionMethod $method, array $result )
    {
        $name = strtolower( $method->getName() );
        if ( !isset( $result[$name] ) )
        {
            $result[$name] = $method;
        }
        else if ( $result[$name]->isAbstract() && !$method->isAbstract() )
        {
            $result[$name] = $method;
        }
        return $result;
    }

    public function getConstructor()
    {
        
    }

    /**
     * @param string $name
     *
     * @return \ReflectionProperty
     */
    public function getProperty( $name )
    {
        if ( isset( $this->_properties[$name] ) )
        {
            return $this->_properties[$name];
        }
        throw new \ReflectionException( sprintf( 'Property %s does not exist', $name ) );
    }

    /**
     * @return array(\ReflectionProperty)
     */
    public function getProperties( $filter = 0 )
    {
        return $this->_properties;
    }

    /**
     * Tries to initializes the properties of the reflected class the first time,
     * it will throw an exception when the properties are already set .
     *
     * @param array(\ReflectionProperty) $properties The properties of this class.
     *
     * @return void
     * @access private
     */
    public function initProperties( array $properties )
    {
        if ( $this->_properties === null )
        {
            $this->_initProperties( $properties );
        }
        else
        {
            throw new \LogicException( 'Property properties already set' );
        }
    }

    /**
     * Initializes the properties of the reflected class.
     *
     * @param array(\ReflectionProperty) $properties The properties of this class.
     *
     * @return void
     */
    private function _initProperties( array $properties )
    {
        $this->_properties = array();
        foreach ( $properties as $property )
        {
            $property->initDeclaringClass( $this );
            $this->_properties[$property->getName()] = $property;
        }
    }
}