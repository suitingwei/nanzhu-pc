<?php

use App\Utils\StringUtil;

class StringUtilTest extends TestCase
{

    public function test_is_char_with_a_single_char()
    {
        $this->assertTrue(StringUtil::isChar('a'));
    }

    public function test_is_char_with_a_number()
    {
        $this->assertFalse(StringUtil::isChar('1'));
    }

    public function test_a_long_sting()
    {
        $this->assertFalse(StringUtil::isChar('asdoansdo'));
    }
}
