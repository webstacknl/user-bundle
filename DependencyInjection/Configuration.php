<?php

namespace Webstack\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Webstack\UserBundle\Form\Type\RegistrationFormType;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('webstack_user');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('user_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('firewall_name')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
                ->booleanNode('use_email_as_username')->defaultTrue()->end()
                ->booleanNode('use_authentication_listener')->defaultTrue()->end()
                ->booleanNode('use_listener')->defaultTrue()->end()
                ->booleanNode('use_flash_notifications')->defaultTrue()->end()
                ->booleanNode('use_username_form_type')->defaultTrue()->end()
                ->arrayNode('from_email')
                    ->isRequired()
                    ->children()
                        ->scalarNode('address')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('sender_name')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('password')
                ->isRequired()
                    ->children()
                        ->scalarNode('min_strenght')->isRequired()->defaultValue(4)->end()
                        ->scalarNode('min_lenght')->isRequired()->defaultValue(8)->end()
                    ->end()
                ->end()
            ->end();

        $this->addRegistrationSection($rootNode);
        $this->addServiceSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addRegistrationSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('registration')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                    ->scalarNode('enabled')->defaultValue(true)->treatNullLike(true)->end()
                    ->arrayNode('confirmation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->scalarNode('template')->defaultValue('@WebstackUser/Registration/email.txt.twig')->end()
                            ->arrayNode('from_email')
                                ->canBeUnset()
                                ->children()
                                    ->scalarNode('address')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('sender_name')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('form')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('type')->defaultValue(RegistrationFormType::class)->end()
                            ->scalarNode('name')->defaultValue('webstack_user_registration_form')->end()
                            ->arrayNode('validation_groups')
                                ->prototype('scalar')->end()
                                ->defaultValue(array('Registration', 'Default'))
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addServiceSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('mailer')->defaultValue('webstack_user.mailer.default')->end()
                            ->scalarNode('user_manager')->defaultValue('webstack_user.user_manager')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
