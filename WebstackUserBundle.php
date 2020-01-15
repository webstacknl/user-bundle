<?php

namespace Webstack\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Webstack\UserBundle\DependencyInjection\Compiler\ValidationPass;

/**
 * Class WebstackUserBundle
 * @author Webstack B.V. <info@webstack.nl>
 */
class WebstackUserBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

//        $container->addCompilerPass(new ValidationPass());

//        $this->addRegisterMappingsPass($container);
    }

//    /**
//     * @param ContainerBuilder $container
//     */
//    private function addRegisterMappingsPass(ContainerBuilder $container)
//    {
//        $mappings = array(
//            realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'FOS\UserBundle\Model',
//        );
//
//        if (class_exists(DoctrineOrmMappingsPass::class)) {
//            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array('fos_user.model_manager_name'), 'fos_user.backend_type_orm'));
//        }
//    }
}