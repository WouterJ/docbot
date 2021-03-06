<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\File;
use Gnugat\Redaktilo\Text;

/**
 * A reviewer checking the correct title level usages.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class TitleLevel extends Base
{
    private $levels = array(
        1 => '=',
        2 => '-',
        3 => '~',
        4 => '.',
        5 => '"',
    );
    private $currentLevel = 1;
    private $startLevelIsDetermined = false;

    public function review(Text $file)
    {
        $this->startLevelIsDetermined = false;

        parent::review($file);
    }

    public function reviewLine($line, $lineNumber, Text $file)
    {
        if (preg_match('/^([\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{3,}$/', trim($line), $data)) {
            $character = $data[1];

            $level = array_search($character, $this->levels);

            if (false === $level) {
                $this->addError('Only =, -, ~, . and " should be used as title underlines');

                return;
            }

            // .inc files are allowed to start with a deeper level.
            $isIncludedFile = $file instanceof File && preg_match('/\.inc/', $file->getFilename());
            if ($isIncludedFile && !$this->startLevelIsDetermined) {
                $this->startLevelIsDetermined = true;
                $this->currentLevel = $level;
            }

            if ($level <= $this->currentLevel) {
                $this->currentLevel = $level;

                return;
            }

            if ($this->currentLevel + 1 !== $level) {
                $this->addError(
                    'The "%underline_char%" character should be used for a title level %level%',
                    array(
                        '%underline_char%' => $this->levels[$this->currentLevel + 1],
                        '%level%' => $this->currentLevel + 1,
                    )
                );
            } else {
                $this->currentLevel = $level;
            }
        }
    }
}
