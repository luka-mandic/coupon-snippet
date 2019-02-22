<?php namespace Mandic\Coupon\Console;

use Mandic\Coupon\Classes\CouponFilter;
use Mandic\Coupon\Models\Coupon;


class CreateNumericCouponValues extends CouponFilter
{

    /**
     * @var string
     */
    protected $signature = 'mandic:createNumericCouponValues';
    /**
     * @var string The console command name.
     */
    protected $name = 'mandic:createNumericCouponValues';

    /**
     * @var string The console command description.
     */
    protected $description = 'Creates numeric values for coupons that can be filtered';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $time_start = microtime(true);

        try {

            $bar = $this->output->createProgressBar(Coupon::where('filter_type', self::PERCENTAGE_VALUE)->count());

            echo "\nStarted processing percentage coupons\n";

            Coupon::where('filter_type', self::PERCENTAGE_VALUE)->chunk(5000, function ($coupons) use ($bar) {
                foreach ($coupons as $coupon) {
                    $coupon->numeric_value = (float)$this->processPercentageCoupon($coupon);
                    $coupon->save();

                    $bar->advance();

                }
            });

            $bar->finish();

            $bar = $this->output->createProgressBar(Coupon::where('filter_type', self::CURRENCY_VALUE)->count());

            echo "\nStarted processing currency coupons\n";

            Coupon::where('filter_type', self::CURRENCY_VALUE)->chunk(5000, function ($coupons) use ($bar) {
                foreach ($coupons as $coupon) {
                    $coupon->numeric_value = (float)$this->processCurrencyCoupon($coupon);
                    $coupon->save();

                    $bar->advance();

                }
            });

            $bar->finish();
        } catch (\Exception $e) {
            \Log::error($e);
        }


        // Display Script End time
        $time_end = microtime(true);

        //dividing with 60 will give the execution time in minutes
        $execution_time = ($time_end - $time_start) / 60;

        //execution time of the script
        echo "\nTotal Execution Time: " . $execution_time . " Mins\n";

    }

    protected function processPercentageCoupon($coupon)
    {
        return preg_replace('/%/', '', $coupon->value);
    }

    protected function processCurrencyCoupon($coupon)
    {
        $numericValue = 0;
        // Extract digits and separators from value, without currencies and symbols
        if(!preg_match('/\d+(,|\.)?\d*(,|\.)?\d*/', $coupon->value, $matches)) {
            return $numericValue;
        }

        // Case 1: Value contains both comma and dot e.g. € 1.299,50
        if (preg_match('/\d*\..*,.*/', $matches[0])) {
            $numericValue = preg_replace('/\./', '', $matches[0]);
            $numericValue = preg_replace('/,/', '.', $numericValue);

            return $numericValue;
        }

        //Case 2: Value contains comma and 1 or 2 digits after e.g. € 23,30
        if (preg_match('/\d+,\d{1,2}$/', $matches[0])) {
            $numericValue = preg_replace('/,/', '.', $matches[0]);

            return $numericValue;
        }

        //Case 3: Value contains comma and 3 or more digits after e.g. € 23,30
        if (preg_match('/\d+,\d{3,}/', $matches[0])) {
            $numericValue = preg_replace('/,/', '', $matches[0]);
            return $numericValue;
        }

        //Case 4: Value contains no commas or dots e.g. 53$
        if (!strpos($matches[0], ',') && !strpos($matches[0], '.')) {
            return $matches[0];
        }

        //Case 5: Value contains dot and 3 or more digits after e.g. $10.000
        if (preg_match('/\d{1,}\.\d{3,}/', $matches[0])) {
            $numericValue = preg_replace('/\./', '', $matches[0]);

            return $numericValue;
        }

        //Case 6: Value contains dot and 1 or 2 digits after e.g. $10.00
        if (preg_match('/\d+\.\d{1,2}/', $matches[0])) {
            return $matches[0];
        }

        //Case 7: Value contains comma and then a minus after e.g. €10,-
        if (substr($matches[0], -1) === ',') {
            $numericValue = preg_replace('/,/', '', $matches[0]);
            return $numericValue;
        }
        return $numericValue;

    }
}
