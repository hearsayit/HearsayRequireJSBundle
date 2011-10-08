<?php

/**
 * Copyright (c) 2011 Hearsay News Products, Inc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
 * SOFTWARE.
 */

namespace Hearsay\RequireJSBundle\Tests\Configuration;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;

/**
 * Unit tests for the helper to generate RequireJS configuration.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigurationGenerated()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
                ->method('getLocale')
                ->will($this->returnValue('fr_FR'));
        $builder = new ConfigurationBuilder($translator, 'js');
        $builder->setOption('option', 'value');
        
        $expected = array(
            'locale' => 'fr_FR',
            'baseUrl' => 'js',
            'option' => 'value',
        );
        $this->assertEquals($expected, $builder->getConfiguration(), 'Unexpected configuration generated');
    }
    
    public function testPathsAdded()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
                ->method('getLocale')
                ->will($this->returnValue('fr_FR'));
        $builder = new ConfigurationBuilder($translator, 'js');
        $builder->setPath('namespace', '/path/to/namespace');
       
        $config = $builder->getConfiguration();
        $this->assertEquals(array('namespace' => '/path/to/namespace'), $config['paths'], 'Did not find expected paths configuration');        
    }
}
