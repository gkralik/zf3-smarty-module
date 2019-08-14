<?php
/**
 * Copyright 2019 Gregor Kralik <g.kralik@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Gregor Kralik <g.kralik@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace GKralik\SmartyModule\Renderer;

use GKralik\SmartyModule\ModuleOptions;
use Interop\Container\ContainerInterface;
use Smarty;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\Resolver\ResolverInterface;

class SmartyRendererFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var ModuleOptions $options */
        $options = $container->get('GKralik\SmartyModule\ModuleOptions');

        /** @var ResolverInterface $resolver */
        $resolver = $container->get('GKralik\SmartyModule\Resolver\SmartyResolver');

        $smartyEngine = new Smarty();

        foreach ($options->getSmartyOptions() as $option => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $option)));
            $callable = [$smartyEngine, $setter];

            if (is_callable($callable)) {
                call_user_func_array($callable, [$value]);
            } elseif (property_exists($smartyEngine, $option)) {
                $smartyEngine->$option = $value;
            }
        }
        unset($option, $value);

        $smartyEngine->setCompileDir($options->getCompileDir());
        $smartyEngine->setCacheDir($options->getCacheDir());
        $smartyEngine->setConfigDir($options->getConfigDir());

        $renderer = new SmartyRenderer($smartyEngine, $resolver);
        $renderer->setHelperPluginManager($container->get('ViewHelperManager'));
        $renderer->setResetAssignedVariablesBeforeRender($options->shouldResetAssignedVariablesBeforeRender());

        return $renderer;
    }
}
