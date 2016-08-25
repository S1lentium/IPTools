<?php

use IPTools\IP;
use IPTools\IPv4;
use IPTools\IPv6;

class IPTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $ipv4String = '127.0.0.1';
        $ipv6String = '2001::';

        $ipv4 = new IPv4($ipv4String);
        $ipv6 = new IPv6($ipv6String);

        $this->assertEquals(inet_pton($ipv4String), $ipv4->inAddr());
        $this->assertEquals(4, $ipv4->getVersion());
        $this->assertEquals(32, $ipv4->getMaxPrefixLength());
        $this->assertEquals(4, $ipv4->getOctetsCount());

        $this->assertEquals(inet_pton($ipv6String), $ipv6->inAddr());
        $this->assertEquals(6, $ipv6->getVersion());
        $this->assertEquals(128, $ipv6->getMaxPrefixLength());
        $this->assertEquals(16, $ipv6->getOctetsCount());
    }    

    /**
     * @dataProvider getTestContructorExceptionData
     * @expectedException Exception
     * @expectedExceptionMessage Invalid IP address format
     */
    public function testConstructorException($string)
    {
        $ip = new IPv4($string);
    }

    public function testProperties()
    {
        $ip = new IPv4('127.0.0.1');

        $this->assertNotEmpty($ip->maxPrefixLength);
        $this->assertNotEmpty($ip->octetsCount);
        $this->assertNotEmpty($ip->reversePointer);

        $this->assertNotEmpty($ip->bin);
        $this->assertNotEmpty($ip->long);
        $this->assertNotEmpty($ip->hex);
    }
    
    /**
     * @dataProvider getTestParseData
     */
    public function testParse($ipString, $expected)
    {
        $ip = IP::parse($ipString);
        $this->assertEquals($expected, (string)$ip);
    }

    /**
     * @dataProvider getParseBinData
     */
    public function testParseBin($bin, $expectedString)
    {
        $ip = IP::parseBin($bin);

        $this->assertEquals($expectedString, (string)$ip);
        $this->assertEquals($bin, $ip->toBin());
    }

    public function testParseLong()
    {
        $ipv4long = '2130706433';
        $ipv4 = IP::parseLong($ipv4long);

        $ipv6Long = '340277174624079928635746076935438991360';
        $ipv6 = IP::parseLong($ipv6Long, 6);

        $this->assertEquals('127.0.0.1', (string)$ipv4);
        $this->assertEquals($ipv4long, $ipv4->toLong());

        $this->assertEquals('ffff::', (string)$ipv6);
        $this->assertEquals($ipv6Long, $ipv6->toLong());
    }

    public function testParseHex()
    {
        $hex = '7f000001';
        $ip = IP::parseHex($hex);

        $this->assertEquals('127.0.0.1', (string)$ip);
        $this->assertEquals($hex, $ip->toHex());

    }

    public function testParseInAddr()
    {
        $inAddr = inet_pton('127.0.0.1');
        $ip = IP::parseInAddr($inAddr);

        $this->assertEquals($inAddr, $ip->inAddr());

        $inAddr = inet_pton('2001::8000:0:0:0');
        $ip = IP::parseInAddr($inAddr);

        $this->assertEquals($inAddr, $ip->inAddr());
    }

    /**
     * @dataProvider getTestNextData
     */
    public function testNext($ip, $step, $expected)
    {
        $object = IP::parse($ip);
        $next = $object->next($step);

        $this->assertEquals($expected, (string) $next);
    }

    /**
     * @dataProvider getTestPrevData
     */
    public function testPrev($ip, $step, $expected)
    {
        $object =IP::parse($ip);
        $prev = $object->prev($step);

        $this->assertEquals($expected, (string) $prev);
    }

    /**
     * @dataProvider getReversePointerData
     */
    public function testReversePointer($ip, $expected)
    {
        $object = IP::parse($ip);
        $reversePointer = $object->getReversePointer();
        $this->assertEquals($expected, $reversePointer);
    }

    public function getTestContructorExceptionData()
    {
        return array(
            array('256.0.0.1'),
            array('127.-1.0.1'),
            array('cake'),
            array('0000:0000:0000:ffff:0127:0000:0000:0001:0000'),
        );
    }    

    public function getTestParseData()
    {
        return array(
            array(2130706433, '127.0.0.1'), //long
            array('0b01111111000000000000000000000001', '127.0.0.1'), //bin
            array('0x7f000001', '127.0.0.1'), //hex,
            array('0x20010000000000008000000000000000', '2001::8000:0:0:0'), //hex
            array('127.0.0.1', '127.0.0.1'),
            array('2001::', '2001::')
        );
    }

    public function getParseBinData()
    {
        return array(
            array(
                '00100000000000010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
                '2001::'
            ),
            array('01111111000000000000000000000001', '127.0.0.1')
        );
    }

    public function getTestNextData()
    {
        return array(
            array('192.168.0.1', 1, '192.168.0.2'),
            array('192.168.0.1', 254, '192.168.0.255'),
            array('192.168.0.1', 255, '192.168.1.0'),
            array('2001::', 1, '2001::1'),
            array('2001::', 65535, '2001::ffff'),
            array('2001::', 65536, '2001::1:0')
        );
    }

    public function getTestPrevData()
    {
        return array(
            array('192.168.1.1', 1, '192.168.1.0'),
            array('192.168.1.0', 1, '192.168.0.255'),
            array('192.168.1.1', 258, '192.167.255.255'),
            array('2001::1', 1, '2001::'),
            array('2001::1:0', 1, '2001::ffff'),
            array('2001::1:0', 65536, '2001::'),
        );
    }

    public function getReversePointerData()
    {
        return array(
            array('192.0.2.5', '5.2.0.192.in-addr.arpa'),
            array('2001:db8::567:89ab', 'b.a.9.8.7.6.5.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa'),
        );
    }
}
