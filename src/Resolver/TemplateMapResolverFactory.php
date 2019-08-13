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

namespace GKralik\SmartyModule\Resolver;

use GKralik\SmartyModule\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Resolver\TemplateMapResolver as BaseTemplateMapResolver;

class TemplateMapResolverFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var ModuleOptions $options */
        $options = $container->get('GKralik\SmartyModule\ModuleOptions');

        /** @var BaseTemplateMapResolver */
        $templateMap = $container->get('ViewTemplateMapResolver');

        $suffix = $options->getSuffix();

        // build map of template files with registered suffix
        $map = [];
        foreach ($templateMap as $name => $path) {
            if ($suffix == pathinfo($path, PATHINFO_EXTENSION)) {
                $map[$name] = $path;
            }
        }

        return new TemplateMapResolver($map);
    }
}
