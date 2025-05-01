<?php

namespace App\Presenter\Cli\Services;

use RuntimeException;

class Spinner
{
    private string $message;
    private array $spinnerChars = ['|', '/', '-', '\\'];
    private bool $running = false;
    private int $pid;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function start(): void
    {
        if ($this->running) {
            return;
        }

        $this->running = true;

        $this->pid = pcntl_fork();

        if ($this->pid === -1) {
            throw new RuntimeException('Could not create spinner');
        }

        if ($this->pid === 0) {
            $i = 0;

            while (true) {
                echo "\r" . $this->spinnerChars[$i % count($this->spinnerChars)] . " " . $this->message;

                usleep(100000);
                $i++;
            }
        }
    }

    public function stop(): void
    {
        if ($this->running && $this->pid) {
            posix_kill($this->pid, SIGTERM);
            pcntl_waitpid($this->pid, $status);

            $this->running = false;

            echo "\r✓ " . $this->message . "\n";
        }
    }
}
