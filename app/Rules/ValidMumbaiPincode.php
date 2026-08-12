<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Services\XpressbeesService;

class ValidMumbaiPincode implements Rule
{
    protected $message = 'This pincode is not serviceable in Mumbai.';

    public function passes($attribute, $value)
    {
        // Must be 6 digits and start with 400 or 401 (basic MMR check)
        if (!preg_match('/^40(0|1)\d{3}$/', $value)) {
            $this->message = 'Only Mumbai/MMR pincodes (starting with 400 or 401) are allowed.';
            return false;
        }

        // Call Xpressbees API to ensure serviceability
        $xpressbees = new XpressbeesService();
        // Check if Xpressbees can pick up/deliver to this pincode. We use a known Mumbai hub (400001) as the origin.
        $isServiceable = $xpressbees->checkServiceability('400001', $value);

        if (!$isServiceable) {
            $this->message = "Pincode {$value} is not serviceable by our courier partner.";
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}
