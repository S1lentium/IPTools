<?php

use IPTools\Range;
use IPTools\Network;
use IPTools\IP;

class RangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestParseData
     */
    public function testParse($data, $expected)
    {
        $range = Range::parse($data);

        $this->assertEquals($expected[0], $range->firstIP);
        $this->assertEquals($expected[1], $range->lastIP);
    }

    /**
     * @dataProvider getTestNetworksData
     */
    public function testGetNetworks($data, $expected)
    {
        $range = Range::parse($data);

        $result = array();

        foreach (Range::parse($data)->getNetworks() as $network) {
            $result[] = (string)$network;
        }

        $this->assertEquals($expected, $result);        
    }

    /**
     * @dataProvider getTestContainsData
     */
    public function testContains($data, $find, $expected)
    {
        $this->assertEquals($expected, Range::parse($data)->contains(new IP($find)));
    }

    /**
     * @dataProvider getTestCountData
     */
    public function testRangeIteration($data, $count)
    {
        $range = Range::parse($data);

        // to break an infinite loop
        $exit = count($range) + 2;

        foreach ($range as $index => $ip) {
            $lastIP = $ip;
            if (--$exit === 0) {
                throw new \RuntimeException('Range iteration caught in an infinite loop');
            }
        }

        $this->assertEquals($range->getLastIP(), $lastIP);
    }

    public function getTestParseData()
    {
        return array(
            array('127.0.0.1-127.255.255.255', array('127.0.0.1', '127.255.255.255')),
            array('127.0.0.1/24', array('127.0.0.0', '127.0.0.255')),
            array('127.*.0.0', array('127.0.0.0', '127.255.0.0')),
            array('127.255.255.0', array('127.255.255.0', '127.255.255.0')),
        );
    }

    public function getTestNetworksData()
    {
        return array(
            array('192.168.1.*', array('192.168.1.0/24')),
            array('192.168.1.208-192.168.1.255', array(
                '192.168.1.208/28',
                '192.168.1.224/27' 
            )),
            array('192.168.1.0-192.168.1.191', array(
                '192.168.1.0/25',
                '192.168.1.128/26' 
            )),
            array('192.168.1.125-192.168.1.126', array(
                '192.168.1.125/32',
                '192.168.1.126/32',
            )),
        );
    }

    public function getTestContainsData()
    {
        return array(
            array('192.168.*.*', '192.168.245.15', true),
            array('192.168.*.*', '192.169.255.255', false),

            /**
             * 10.10.45.48 --> 00001010 00001010 00101101 00110000 
             * the last 0000 leads error
             */
            array('10.10.45.48/28', '10.10.45.58', true),

            array('2001:db8::/64', '2001:db8::ffff', true),
            array('2001:db8::/64', '2001:db8:ffff::', false),
        );
    }

    public function getTestCountData()
    {
        return array(
            array('192.168.2.*', 256),
            array('2001:db8::/120', 256),
        );
    }
}
