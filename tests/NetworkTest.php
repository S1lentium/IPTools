<?php

use IPTools\Network;
use IPTools\IP;

class NetworkTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $ipv4 = new IP('127.0.0.1');
        $ipv4Netmask = new IP('255.255.255.0');

        $ipv6 = new IP('2001::');
        $ipv6Netmask = new IP('ffff:ffff:ffff:ffff:ffff:ffff:ffff::');

        $ipv4Network = new Network($ipv4, $ipv4Netmask);
        $ipv6Network = new Network($ipv6, $ipv6Netmask);

        $this->assertEquals('127.0.0.0/24', (string)$ipv4Network);
        $this->assertEquals('2001::/112', (string)$ipv6Network);
    }

    public function testProperties()
    {
        $network = Network::parse('127.0.0.1/24');

        $network->ip = new IP('192.0.0.2');

        $this->assertEquals('192.0.0.2', $network->ip);
        $this->assertEquals('192.0.0.0/24', (string)$network);
        $this->assertTrue(is_array($network->info));
    }

    /**
     * @dataProvider getTestParseData
     */
    public function testParse($data, $expected)
    {
        $network = Network::parse($data);
        $this->assertEquals($expected, (string)$network);
    }

    /**
     * @dataProvider getPrefixData
     */
    public function testPrefix2Mask($prefix, $version, $mask)
    {
        $this->assertEquals($mask, Network::prefix2netmask($prefix, $version));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Wrong IP version
     */
    public function testPrefix2MaskWrongIPVersion()
    {
        Network::prefix2netmask('128', 'ip_version');
    }

    /**
     * @dataProvider getInvalidPrefixData
     * @expectedException Exception
     * @expectedExceptionMessage Invalid prefix length
     */
    public function testPrefix2MaskInvalidPrefix($prefix, $version)
    {
        Network::prefix2netmask($prefix, $version);
    }

    /**
     * @dataProvider getPrefixData
     */
    public function testGetInfo()
    {
        $ipv4Network = Network::parse('192.168.0.0/24');
        $ipv6Network = Network::parse('ffff:ffff:ffff:ffff::/64');

        $this->assertTrue(is_array($ipv4Network->getInfo()));
        $this->assertTrue(is_array($ipv6Network->getInfo()));
    }

    /**
     * @dataProvider getExcludeData
     */
    public function testExclude($network, $exclude, $expected)
    {
        $excluded = Network::parse($network)->exclude($exclude);

        $result = array();

        foreach($excluded as $network) {
            $result[] =(string)$network;
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Exclude subnet not within target network
     */
    public function testExcludeException()
    {
        Network::parse('192.0.2.0/28')->exclude('192.0.3.0/24');
    }

    public function getTestParseData()
    {
        return array(
            array('192.168.0.54/24', '192.168.0.0/24'),
            array('2001::2001:2001/32', '2001::/32'),
            array('127.168.0.1 255.255.255.255', '127.168.0.1/32'),
            array('1234::1234', '1234::1234/128'),
        );
    }

    public function getPrefixData()
    {
        return array(
            array('24', IP::IP_V4, IP::parse('255.255.255.0')),
            array('32', IP::IP_V4, IP::parse('255.255.255.255')),
            array('64', IP::IP_V6, IP::parse('ffff:ffff:ffff:ffff::')),
            array('128', IP::IP_V6, IP::parse('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'))
        );
    }

    public function getInvalidPrefixData()
    {
        return array(
            array('-1', IP::IP_V4),
            array('33', IP::IP_V4),
            array('prefix', IP::IP_V4),
            array('-1', IP::IP_V6),
            array('129', IP::IP_V6),
        );
    }

    public function getExcludeData()
    {
        return array(
            array('192.0.2.0/28', '192.0.2.1/32', 
                array(
                    '192.0.2.0/32',
                    '192.0.2.2/31',
                    '192.0.2.4/30',
                    '192.0.2.8/29',
                )
            ),
        );
    }
}
