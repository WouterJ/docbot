<?php

namespace Docbot\Reporter;

use Docbot\Reporter;
use Gnugat\Redaktilo\File;
use Gnugat\Redaktilo\Text;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A reporter that prints into the console.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Console implements Reporter
{
    /** @var OutputInterface */
    private $output;
    /** @var FormatterHelper */
    private $formatterHelper;

    public function __construct(OutputInterface $output)
    {
        $this->formatterHelper = new FormatterHelper();
        $this->output = $output;
    }

    public function handle(ConstraintViolationListInterface $constraintViolationList, Text $file)
    {
        if ($file instanceof File) {
            $this->printFilename($file);
        }

        if (0 === $errorCount = count($constraintViolationList)) {
            $this->output->writeln('');
            $this->outputBlock('Perfect! No errors were found.');

            return self::SUCCESS;
        }

        $currentLineNumber = 0;
        /** @var ConstraintViolation $violation */
        foreach ($constraintViolationList as $violation) {
            $lineNumber = $this->getLineNumber($violation);
            if ($lineNumber !== $currentLineNumber) {
                $this->output->writeln('');
                $this->printLine($file, $lineNumber);

                $currentLineNumber = $lineNumber;
            }

            $indent = 3 + strlen($lineNumber);
            $this->printError($violation, $indent);
        }

        $this->output->writeln('');
        $this->outputBlock(sprintf('Found %d error%s.', $errorCount, $errorCount === 1 ? '' : 's'), false);

        return self::ERROR;
    }

    private function outputBlock($message, $success = true)
    {
        $color = $success ? 'green' : 'red';
        $message = ($success ? '[OK]' : '[ERROR]').' '.$message;

        $this->output->writeln($this->formatterHelper->formatBlock($message, 'bg='.$color, true));
    }

    private function getLineNumber(ConstraintViolation $violation)
    {
        if (!preg_match('/lines\[(\d+)\]/', $violation->getPropertyPath(), $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot get line number of string "%s". Format has to be: line[%line_number%]',
                $violation->getPropertyPath()
            ));
        }

        return intval($matches[1]);
    }

    private function printFilename(File $file)
    {
        $filename = str_replace(getcwd(), '', $file->getFilename());

        $this->output->writeln(array(
            '<fg=blue>',
            $filename,
            str_repeat('=', strlen($filename)).'</>',
        ));
    }

    private function printLine(Text $file, $lineNumber)
    {
        $line = $file->getLine($lineNumber - 1);

        $this->output->writeln('<comment>['.$lineNumber.']</comment> "'.$line.'"');
    }

    private function printError(ConstraintViolation $violation, $indent)
    {
        $this->output->writeln(str_repeat(' ', $indent).'- <fg=red>'.$violation->getMessage().'</>');
    }
}
