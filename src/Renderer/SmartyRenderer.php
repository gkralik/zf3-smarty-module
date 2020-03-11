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

use ArrayObject;
use GKralik\SmartyModule\ModuleOptions;
use Smarty;
use SmartyException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception\DomainException;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;

class SmartyRenderer implements RendererInterface
{

    /** @var Smarty */
    private $smarty;

    /** @var ResolverInterface */
    private $resolver;

    /** @var bool */
    private $resetAssignedVariablesBeforeRender = true;

    /** @var HelperPluginManager */
    private $helperPluginManager;

    /**
     * @return bool
     */
    public function shouldResetAssignedVariablesBeforeRender()
    {
        return $this->resetAssignedVariablesBeforeRender;
    }

    /**
     * @param bool $resetAssignedVariablesBeforeRender
     */
    public function setResetAssignedVariablesBeforeRender(bool $resetAssignedVariablesBeforeRender)
    {
        $this->resetAssignedVariablesBeforeRender = $resetAssignedVariablesBeforeRender;
    }

    /** @var array Plugins cache. */
    private $pluginsCache;

    /**
     * SmartyRenderer constructor.
     *
     * @param Smarty            $smarty
     * @param ResolverInterface $resolver
     */
    public function __construct(Smarty $smarty, ResolverInterface $resolver)
    {
        $this->smarty   = $smarty;
        $this->resolver = $resolver;
    }

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->smarty;
    }

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     *
     * @param ResolverInterface $resolver
     *
     * @return RendererInterface
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Can the template be rendered?
     *
     * A template can be rendered if the attached resolver can resolve the given
     * template name.
     */
    public function canRender($nameOrModel)
    {
        if ($nameOrModel instanceof ModelInterface) {
            $nameOrModel = $nameOrModel->getTemplate();
        }

        $file = $this->resolver->resolve($nameOrModel);
        return $file ? true : false;
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string|ModelInterface   $nameOrModel The script/resource process, or a view model
     * @param null|array|\ArrayAccess $values      Values to use during rendering
     *
     * @return string The script output.
     * @throws SmartyException
     */
    public function render($nameOrModel, $values = null)
    {
        if ($nameOrModel instanceof ModelInterface) {
            $model = $nameOrModel;
            $nameOrModel = $model->getTemplate();

            if (empty($nameOrModel)) {
                throw new DomainException(sprintf(
                    '%s: received %s argument, but template is empty',
                    __METHOD__,
                    ModelInterface::class
                ));
            }

            foreach ($model->getOptions() as $setting => $value) {
                $method = 'set' . $setting;
                if (method_exists($this, $method)) {
                    call_user_func_array([$this, $method], [$value]);
                }
            }
            unset($setting, $value);

            // give view model awareness via ViewModel helper
            /** @var ViewModelHelper $helper */
            $helper = $this->plugin('view_model');
            $helper->setCurrent($model);

            $values = $model->getVariables();
            unset($model);
        }

        $file = $this->resolver->resolve($nameOrModel);
        if (!$file) {
            throw new DomainException(sprintf(
                '%s: unable to resolve template %s',
                __METHOD__,
                $nameOrModel
            ));
        }

        if ($values instanceof ArrayObject) {
            $values = $values->getArrayCopy();
        }

        $values['this'] = $this;

        if ($this->shouldResetAssignedVariablesBeforeRender()) {
            $this->smarty->clearAllAssign();
        }

        $this->smarty->assign($values);

        // add current dir to allow including partials without full path
        $this->smarty->addTemplateDir(dirname($file));

        return $this->smarty->fetch($file);
    }

    /**
     * Sets the HelperPluginManagers renderer instance to $this.
     *
     * @param HelperPluginManager $helperPluginManager
     *
     * @return SmartyRenderer
     */
    public function setHelperPluginManager(HelperPluginManager $helperPluginManager)
    {
        $helperPluginManager->setRenderer($this);
        $this->helperPluginManager = $helperPluginManager;

        return $this;
    }

    /**
     * @return HelperPluginManager
     */
    public function getHelperPluginManager()
    {
        if ($this->helperPluginManager === null) {
            $this->setHelperPluginManager(new HelperPluginManager(new ServiceManager()));
        }

        return $this->helperPluginManager;
    }

    public function __clone()
    {
        $this->smarty = clone $this->smarty;
    }

    /**
     * Magic method overloading
     *
     * Proxies calls to the attached HelperPluginManager.
     * * Helpers without an __invoke() method are simply returned.
     * * Helpers with an __invoke() method will be called and their return
     *   value is returned.
     *
     * A cache is used to speed up successive calls to the same helper.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!isset($this->pluginsCache[$name])) {
            $this->pluginsCache[$name] = $this->plugin($name);
        }
        if (is_callable($this->pluginsCache[$name])) {
            return call_user_func_array($this->pluginsCache[$name], $arguments);
        }
        return $this->pluginsCache[$name];
    }

    /**
     * Retrieve plugin instance.
     *
     * Proxies to HelperPluginManager::get.
     *
     * @param string $name Plugin name.
     * @param array $options Plugin options. Passed to the plugin constructor.
     * @return \Laminas\View\Helper\AbstractHelper
     */
    public function plugin($name, array $options = null)
    {
        return $this->getHelperPluginManager()
            ->setRenderer($this)
            ->get($name, $options);
    }
}
