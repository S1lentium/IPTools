<?php

use IPTools\Network;
use IPTools\IP;

class NetworkTest extends \PHPUnit_Framework_TestCase
{
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
}
