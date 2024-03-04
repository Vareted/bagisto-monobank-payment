<?php

namespace Vareted\Monobank\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Route;

class Monobank extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'monobank';

    public function getRedirectUrl(): string
    {
        return route('monobank.redirect');
    }

    /**
     * Returns payment method image.
     *
     * @return string
     */
    public function getImage()
    {
        $url = $this->getConfigData('logo');

        return $url ? Storage::url($url) : bagisto_asset('images/money-transfer.png', 'shop');
    }
}
