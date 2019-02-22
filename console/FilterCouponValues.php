<?php namespace Mandic\Coupon\Console;

use Mandic\Coupon\Classes\CouponFilter;
use Mandic\Coupon\Models\Coupon;


class FilterCouponValues extends CouponFilter
{

    /**
     * @var string
     */
    protected $signature = 'mandic:filterCouponValues';
    /**
     * @var string The console command name.
     */
    protected $name = 'mandic:filterCouponValues';

    /**
     * @var string The console command description.
     */
    protected $description = 'Filters coupons by value';
    protected $percentageCoupons = [];
    protected $currencyCoupons = [];

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {

        $time_start = microtime(true);

        $bar = $this->output->createProgressBar(Coupon::whereNotIn('status', ['expired', 'trash'])->count());

        try {
            Coupon::whereNotIn('status', [Coupon::STATUS_TRASH, Coupon::STATUS_EXPIRED])->chunk(5000, function ($coupons) use ($bar) {
                foreach ($coupons as $coupon) {
                    $this->check($coupon);

                    $bar->advance();

                }

                Coupon::whereIn('id', $this->currencyCoupons)->update(['filter_type' => self::CURRENCY_VALUE]);
                Coupon::whereIn('id', $this->percentageCoupons)->update(['filter_type' => self::PERCENTAGE_VALUE]);

                $this->currencyCoupons = [];
                $this->percentageCoupons = [];

            });
            $bar->finish();

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        // Display Script End time
        $time_end = microtime(true);

        //dividing with 60 will give the execution time in minutes
        $execution_time = ($time_end - $time_start) / 60;

        //execution time of the script
        echo "\nTotal Execution Time: " . $execution_time . " Mins\n";

    }

    public function check($coupon)
    {
        $percentageRegex = '^\d{1,3}%$';

        $currencies = '(EUR|GBP|USD|SEK|INR|BRL|RUB|NOK|PLN)';
        $currencySymbols = '(€|\$|£|kr|kr\.|Kr|KR|Rs\.|R\$|руб|zł)';

        if (preg_match("/^$currencies?$currencySymbols? ?\d{1,5}(,|\.)?\d{0,3}(,|\.)?\d{0,2} ?$currencySymbols?-?$currencies?$/", $coupon->value)) {
            $this->currencyCoupons[] = $coupon->id;
            return;
        }

        if (preg_match("/{$percentageRegex}/", $coupon->value)) {
            $this->percentageCoupons[] = $coupon->id;
            return;
        }
    }


}
