<?php

namespace App\Service;

use Symfony\Component\Process\Process;

class CommandRunner
{
    /**
     * @param string $command
     * @param array $arguments
     * @return false|string
     */
    public function runCommand(string $command, array $arguments = [])
    {
        $commands = [
            'php',
            'bin/console',
            $command
        ];

        $arguments = array_merge($commands, $arguments);

        $process = new Process($arguments);
        $process->setWorkingDirectory(getcwd() . "/../");
        $process->run();

        if (!$process->isSuccessful()) {
            return $process->getErrorOutput();
        }

        return 'Command was executed';
    }
}
