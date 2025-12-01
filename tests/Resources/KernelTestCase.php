<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Resources;

use ZJKiza\HttpResponseValidator\Tests\Resources\App\ZJKizaHttpResponseValidatorBundleTestKernel;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return ZJKizaHttpResponseValidatorBundleTestKernel::class;
    }
}
