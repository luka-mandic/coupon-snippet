<?php

namespace Mandic\Coupon\Classes;

use Illuminate\Console\Command;

/**
 * Class CouponFilter
 * @package Mandic\Coupon\Classes
 *
 * We need to filter the coupons by value (either price or percentage) so that they can be used in a price filter slider
 *
 * The flag is set in the filter_type column
 *
 * 0 => no value
 * 1 => currency value
 * 2 => percentage value
 *
 */
abstract class CouponFilter extends Command
{

    const NO_VALUE = 0;
    const CURRENCY_VALUE = 1;
    const PERCENTAGE_VALUE = 2;

}