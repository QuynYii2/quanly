<?php
/**
 * Created by PhpStorm.
 * User: ChienKV
 * Date: 8/14/2015
 * Time: 11:00 PM
 */
function smarty_modifier_number_format($number, $decimals=2, $dec_point=".", $thousands_sep="'")
{
    return number_format($number, $decimals, $dec_point, $thousands_sep);
}