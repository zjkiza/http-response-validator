<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use ZJKiza\HttpResponseValidator\DependencyInjection\Extension as ExtensionDependency;

final class ZJKizaHttpResponseValidatorBundle extends Bundle
{
    public function getContainerExtension(): ExtensionDependency
    {
        return new ExtensionDependency();
    }
}
