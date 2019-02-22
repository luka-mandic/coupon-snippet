# Code snippet

Two console commands used to filter out coupons that had usable values.

Values ranged from percentage, currency and random strings, so we had to extract the ones we could actually use.

Regex is used to first separate coupons into 3 categories (percentage, currency and no value). Then the actual numeric value is extracted so it can be used later.