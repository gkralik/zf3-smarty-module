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

use GKralik\SmartyModule\ModuleOptions;
use GKralik\SmartyModule\ModuleOptionsFactory;
use GKralik\SmartyModule\Renderer\SmartyRenderer;
use GKralik\SmartyModule\Renderer\SmartyRendererFactory;
use GKralik\SmartyModule\Resolver\SmartyResolverFactory;
use GKralik\SmartyModule\Resolver\TemplateMapResolverFactory;
use GKralik\SmartyModule\Resolver\TemplatePathStackResolverFactory;
use GKralik\SmartyModule\Strategy\SmartyStrategy;
use GKralik\SmartyModule\Strategy\SmartyStrategyFactory;

return [
    'zf3-smarty-module' => [
        /** Template suffix */
        'suffix'         => 'tpl',
        /** Directory for compiled templates */
        'compile_dir'    => getcwd() . '/cache/templates_c',
        /** Directory for cached templates */
        'cache_dir'      => getcwd() . '/cache/templates',
        /** Path to smarty config file */
        'config_dir'     => null,
        /** Additional smarty engine options */
        'smarty_options' => [],
    ],
    /**
     * Register services.
     */
    'service_manager'   => [
        'factories' => [
            ModuleOptions::class                                => ModuleOptionsFactory::class,
            SmartyStrategy::class                               => SmartyStrategyFactory::class,
            SmartyRenderer::class                               => SmartyRendererFactory::class,
            'GKralik\SmartyModule\Resolver\SmartyResolver'      => SmartyResolverFactory::class,
            'GKralik\SmartyModule\Resolver\TemplateMapResolver' => TemplateMapResolverFactory::class,
            'GKralik\SmartyModule\Resolver\TemplatePathStack'   => TemplatePathStackResolverFactory::class,
        ],
    ],
    /**
     * Register view strategy with the view manager.
     * REQUIRED.
     */
    'view_manager'      => [
        'strategies' => [
            SmartyStrategy::class,
        ],
    ],
];
