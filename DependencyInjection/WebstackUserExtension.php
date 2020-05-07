<?php

namespace Webstack\UserBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class WebstackUserExtension
 */
class WebstackUserExtension extends Extension
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        foreach (array('doctrine', 'util', 'security') as $basename) {
            $loader->load(sprintf('%s.xml', $basename));
        }

        $container->setParameter('webstack_user.model.user.class', $config['user_class']);
        $container->setParameter('webstack_user.model.user.class.email_as_username', $config['use_email_as_username']);

        if (isset($config['password'])) {
            $container->setParameter('webstack_user.security.password.min_strength', $config['password']['min_strength']);
            $container->setParameter('webstack_user.security.password.min_length', $config['password']['min_length']);
        }

        if (($config['registration']['enabled'])) {
            $this->loadRegistration($config['registration'], $container, $loader, $config['from_email']);
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader $loader
     * @param array $fromEmail
     * @throws Exception
     */
    private function loadRegistration(array $config, ContainerBuilder $container, XmlFileLoader $loader, array $fromEmail): void
    {
        $loader->load('registration.xml');

        $container->setParameter('webstack_user.registration.form.name', $config['form']['name']);
        $container->setParameter('webstack_user.registration.form.type', $config['form']['type']);
        $container->setParameter('webstack_user.registration.form.validation_groups', $config['form']['validation_groups']);

        if (isset($config['confirmation']['from_email'])) {
            $fromEmail = $config['confirmation']['from_email'];
            unset($config['confirmation']['from_email']);
        }

        $container->setParameter('webstack_user.registration.from_email', $fromEmail);
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @param array $map
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (array_key_exists($name, $config)) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @param array $namespaces
     */
    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!array_key_exists($ns, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }
}
