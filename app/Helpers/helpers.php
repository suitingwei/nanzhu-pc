<?php

/**
 * 有用的辅助函数
 */

/**
 * 判断一个class是否使用了指定的trait
 *
 * @return boolean
 */
if (!function_exists('class_use_trait')) {

    function class_use_trait($class, $trait)
    {
        return in_array($trait, class_uses($class));
    }
}
