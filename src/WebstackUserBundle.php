<?php

namespace Webstack\UserBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebstackUserBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->addRegisterMappingsPass($container);
    }

    private function addRegisterMappingsPass(ContainerBuilder $container): void
    {
        $mappings = [
            realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'Webstack\UserBundle\Model',
        ];

        if (class_exists(DoctrineOrmMappingsPass::class)) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, ['webstack_user.model_manager_name'], 'webstack_user.backend_type_orm'));
        }
    }
}
