<?php
/**
 * This file is part of the static reflection component.
 *
 * PHP Version 5
 *
 * Copyright (c) 2009-2011, Manuel Pichler <mapi@pdepend.org>.
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
 * @package   pdepend\reflection\resolvers
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2009-2011 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://pdepend.org/
 */

namespace pdepend\reflection\resolvers;

require_once __DIR__ . '/../BaseTest.php';

/**
 * Test cases for the autoloading array resolver.
 *
 * @category  PHP
 * @package   pdepend\reflection\resolvers
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2009-2011 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 */
class AutoloadArrayResolverTest extends \pdepend\reflection\BaseTest
{
    /**
     * @return void
     * @covers \pdepend\reflection\interfaces\SourceResolver
     * @covers \pdepend\reflection\resolvers\AutoloadArrayResolver
     * @group reflection
     * @group reflection::resolvers
     * @group unittest
     */
    public function testGetPathnameWithoutNamespaceReturnsExpectedResult()
    {
        $resolver = new AutoloadArrayResolver(
            array(
                'Bar' => '/foo/Bar.php',
                'Baz' => '/foo/Baz.php',
            )
        );

        self::assertEquals( '/foo/Baz.php', $resolver->getPathnameForClass( 'Baz' ) );
    }

    /**
     * @return void
     * @covers \pdepend\reflection\interfaces\SourceResolver
     * @covers \pdepend\reflection\resolvers\AutoloadArrayResolver
     * @group reflection
     * @group reflection::resolvers
     * @group unittest
     */
    public function testGetPathnameWithNamespaceReturnsExpectedResult()
    {
        $resolver = new AutoloadArrayResolver(
            array(
                '\foo\Bar'    => '/foo/Bar.php',
                '\foobar\Baz' => '/foo/Baz.php',
            )
        );

        self::assertEquals( '/foo/Bar.php', $resolver->getPathnameForClass( '\foo\Bar' ) );
    }

    /**
     * @return void
     * @covers \pdepend\reflection\interfaces\SourceResolver
     * @covers \pdepend\reflection\resolvers\AutoloadArrayResolver
     * @group reflection
     * @group reflection::resolvers
     * @group unittest
     */
    public function testGetPathnameNormalizedNamespacedNames()
    {
        $resolver = new AutoloadArrayResolver(
            array(
                '\foo\Bar'    => '/foo/Bar.php',
                '\foobar\Baz' => '/foo/Baz.php',
            )
        );

        self::assertEquals( '/foo/Bar.php', $resolver->getPathnameForClass( 'foo\Bar' ) );
    }

    /**
     * @return void
     * @covers \pdepend\reflection\interfaces\SourceResolver
     * @covers \pdepend\reflection\resolvers\AutoloadArrayResolver
     * @covers \pdepend\reflection\exceptions\PathnameNotFoundException
     * @group reflection
     * @group reflection::resolvers
     * @group unittest
     * @expectedException \pdepend\reflection\exceptions\PathnameNotFoundException
     */
    public function testGetPathnameThrowsExceptionWhenNoMatchExistsForTheGivenClassName()
    {
        $resolver = new AutoloadArrayResolver();
        $resolver->getPathnameForClass( 'Foo' );
    }
}