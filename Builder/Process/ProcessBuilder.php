<?php

declare(strict_types=1);

namespace App\Builder\Process;

use Symfony\Component\Process\Process;

/**
 * Class ProcessBuilder
 * @package App\Builder\Process
 */
final class ProcessBuilder
{
    /**
     * @var string
     */
    private string $commandName = '';
    /**
     * @var string
     */
    private string $params = '';
    /**
     * @var Process|null
     */
    private ?Process $process = null;

    /**
     * @return $this
     */
    public function createProcess(): self
    {
        $this->process = (new Process([
            'symfony',
            'console',
            $this->commandName,
            $this->params
        ]));

        return $this;
    }

    /**
     * @return Process
     */
    public function build(): Process
    {
        return $this->process;
    }

    /**
     * @return $this
     */
    public function disableOutput(): self
    {
        $this->process->disableOutput();
        return $this;
    }

    /**
     * @param string $cwd
     * @return $this
     */
    public function setWorkingDirectory(string $cwd): self
    {
        $this->process->setWorkingDirectory($cwd);

        return $this;
    }

    /**
     * @param string $commandName
     * @return $this
     */
    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    /**
     * @param string $params
     * @return $this
     */
    public function setParams(string $params): self
    {
        $this->params = $params;

        return $this;
    }
}
