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

namespace GKralik\SmartyModule\Strategy;

use GKralik\SmartyModule\Renderer\SmartyRenderer;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\ViewEvent;

class SmartyStrategy extends AbstractListenerAggregate
{
    /** @var SmartyRenderer*/
    private $renderer;

    /**
     * SmartyStrategy constructor.
     *
     * @param SmartyRenderer $renderer
     */
    public function __construct(SmartyRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, [$this, 'selectRenderer'], $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'injectResponse'], $priority);
    }

    /**
     * Check if the renderer can load the requested template.
     *
     * @param ViewEvent $e
     * @return bool|SmartyRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        if (!$this->renderer->canRender($e->getModel()->getTemplate())) {
            return false;
        }

        return $this->renderer;
    }

    /**
     * Inject the response from the renderer.
     *
     * @param ViewEvent $e
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            // not our renderer
            return;
        }

        $result = $e->getResult();
        $response = $e->getResponse();

        $response->setContent($result);
    }
}
