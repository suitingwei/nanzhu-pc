<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Collection;

class StringUtil
{

    /**
     * 返回字符串的拼音形式
     *
     * @param $string
     *
     * @return string
     */
    public static function pinyin($string)
    {
        return implode('', self::pinyinArray($string));
    }

    /**
     * 返回拼音的数组
     *
     * @param $string
     *
     * @return string
     */
    public static function pinyinArray($string)
    {
        return pinyin($string);
    }

    /**
     * 获取第一个字符
     * 默认返回大写字母
     *
     * @param      $string
     * @param bool $lowerCase
     *
     * @return string
     */
    public static function firstChar($string, $lowerCase = false)
    {
        $pinyinString = self::pinyin($string);

        if (empty($pinyinString)) {
            return '';
        }

        return $lowerCase ? strtolower($pinyinString[0]) : strtoupper($pinyinString[0]);
    }

    /**
     * 判断一个字符是否字母
     * a-z/A-Z
     *
     * @param $string
     *
     * @return int
     */
    public static function isChar($string)
    {
        return (boolean)preg_match("/^[a-zA-Z]{1}$/i", $string);
    }

    /**
     * 判断一个字符不是字母
     * a-z/A-Z
     *
     * @param $string
     *
     * @return bool
     */
    public static function isNotChar($string)
    {
        return !self::isChar($string);
    }

    /**
     * 返回A-Z
     *
     * @return array
     */
    public static function chars()
    {
        static $asciiBegin = 65;  //ascii码 A
        static $asciiEnd = 90;   //ascii码 Z

        $charsArray = [];

        for ($i = $asciiBegin; $i <= $asciiEnd; $i++) {
            $charsArray[] = chr($i);
        }

        return $charsArray;
    }


    /**
     * 字符串比较大小,可能出现#字符
     * A-Z依照自然顺序排序,#最大
     * 用于uksort的Closure,所以返回为-1/0/1
     *
     * @param $first
     * @param $second
     *
     * @return int
     */
    public static function compareWithSharp($first, $second)
    {
        //如果相等,A==A,B==B, #==#
        if ($first === $second) {
            return 0;
        }
        //如果两者不相等,且存在#,#大
        if ($first == '#') {
            return 1;
        }
        if ($second == '#') {
            return -1;
        }

        //如果不存在#,比较ascii码大小即可
        return ord($first) - ord($second);
    }

    /**
     * 按照首字母进行分组(大写字母)
     * 1.排序后返回格式如下:
     *      [
     *         A => [x,y,z...],
     *         B => [x,y,z...],
     *         C => [x,y,z...],
     *         ...
     *         Z => [x,y,z...],
     *         # => [x,y,z...],
     *      ]
     *  2. 数组key为大写字母A-Z以及#
     *  3. 首字母不是字母的将被转换成#
     *  4. 所有#都将被排序到最后
     *  5. 用于进行分组的字段名必须name
     *
     * @param  array $items
     *
     * @param string $groupKey
     *
     * @return array
     */
    public static function groupByFirstChar($items,$groupKey='name')
    {
        if($items instanceof  Collection){
            $items = $items->all();
        }

        $result = [];

        array_map(function ($item) use (&$result,$groupKey) {
            //进行首字母分组的属性
            //$user->FNAME / $item->name
            $firstChar = strtoupper(StringUtil::firstChar($item->$groupKey));

            $firstChar = StringUtil::isChar($firstChar) ? $firstChar : '#';

            $result[$firstChar][] = $item;

        }, $items);

        uksort($result, function ($first, $second) {
            return StringUtil::compareWithSharp($first, $second);
        });

        return $result;
    }


    /**
     * 返回所有A-Z以及#
     * 1.#在最后
     *
     * @return array
     */
    public static function charsWithSharp()
    {
        $chars = self::chars();

        array_push($chars, '#');

        return $chars;
    }

}
