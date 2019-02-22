<?php

namespace Mandic\Coupon\Models;

use October\Rain\Database\Model;

/**
 * @property integer $id
 * @property string $status
 * @property string $value
 * @property integer $filter_type
 * @property float $numeric_value
 */
abstract class BaseCoupon extends Model
{
    const STATUS_PUBLISH = 'publish';
    const STATUS_DRAFT = 'draft';
    const STATUS_TRASH = 'trash';
    const STATUS_REVIEW = 'review';
    const STATUS_EXPIRED = 'expired';
    const STATUS_FUTURE = 'future';
    const STATUS_PENDING = 'pending';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'coupons';

    /**
     * @var array
     */
    public $fillable = [
        'status',
        'value',
        'filter_type',
        'numeric_value',
    ];
}
