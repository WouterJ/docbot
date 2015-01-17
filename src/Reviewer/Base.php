<?php

namespace Stoffer\Reviewer;

use Stoffer\Editor;
use Stoffer\Reviewer;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class Base implements Reviewer
{
    /** @var EventManagerInterface */
    private $eventManager;

    public function review(Event $event)
    {
        $event->getParams()->file->map(array($this, 'reviewLine'));
    }

    abstract public function reviewLine($line, $lineNumber, $file);

    protected function reportError($message, $line, $fileName, $lineNumber)
    {
        $params = new \stdClass();
        $params->message = $message;
        $params->line = $line;
        $params->lineNumber = $lineNumber;
        $params->fileName = $fileName;

        $this->getEventManager()->trigger('error_reported', 'reviewer', $params);
    }

    /**
     * {@inheritDocs}
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritDocs}
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = new EventManager(array('reviewer', get_class($this)));
        }

        return $this->eventManager;
    }

}
