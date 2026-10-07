<?php

namespace SpipLeague\Composer\Command;

use Composer\Pcre\Preg;
use Composer\Util\Platform;
use SpipLeague\Composer\SpipPaths;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'spip:local',
    description: 'Allows running commands in a local context',
    aliases: ['local', 'l'],
)]
/**
 * Inspired by Composer\Command\GlobalCommand
 * @since 0.6.0
 */
class LocalCommand extends AbstractSpipCommand
{
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('command-name', InputArgument::REQUIRED, ''),
                new InputArgument('args', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, ''),
            ])
            ->setHelp(
                'Use this command as a wrapper to run other Composer commands' .
                ' within the local context of a ' . SpipPaths::LOCAL_COMPOSER . ' composer file.
            ',
            );
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $rootDir = $this->getRootDir();
        $composerFile = $rootDir . '/' . SpipPaths::LOCAL_COMPOSER;
        if (!\file_exists($composerFile)) {
            $output->writeln('Creating ' . SpipPaths::LOCAL_COMPOSER . ' ...');
            \copy($rootDir . '/composer.json', $composerFile);
        }
        Platform::putEnv('COMPOSER', SpipPaths::LOCAL_COMPOSER);

        // extract real command name
        $tokens = Preg::split('{\s+}', $input->__toString());
        $args = [];
        foreach ($tokens as $token) {
            if ($token && $token[0] !== '-') {
                $args[] = $token;
                if (count($args) >= 2) {
                    break;
                }
            }
        }

        // show help for this command if no command was found
        if (count($args) < 2) {
            return parent::run($input, $output);
        }

        // create new input without "local" command prefix
        $input = new StringInput(Preg::replace('{\bl(?:o(?:c(?:a(?:l)?)?)?)?\b}', '', $input->__toString(), 1));

        $this->getApplication()
            ->resetComposer();

        return $this->getApplication()
            ->run($input, $output);
    }

    public function isProxyCommand(): bool
    {
        return true;
    }
}
