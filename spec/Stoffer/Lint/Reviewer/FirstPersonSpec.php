<?php

namespace spec\Stoffer\Lint\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Promise\Event as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class FirstPersonSpec extends ReviewerBehaviour
{
    function it_finds_first_person_usage(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'I wrote this line!',
                'In this line is everything correct.',
                'But let\'s screw it up here.',
            ))
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'The first person ("I", "we", "let\'s") should always be avoided', 1);
        PredictThatReviewer::shouldReportError($eventManager, 'The first person ("I", "we", "let\'s") should always be avoided', 3);

        $this->review($event);
    }
}
