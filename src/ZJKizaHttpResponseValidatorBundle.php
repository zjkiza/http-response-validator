<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ZJKiza\HttpResponseValidator\DependencyInjection\Extension;
use ZJKiza\HttpResponseValidator\DependencyInjection\Extension as ExtensionDependency;

final class ZJKizaHttpResponseValidatorBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getContainerExtension(): ExtensionDependency
    {
        return new Extension();
    }
}
