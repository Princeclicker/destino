<?php

namespace SpipLeague\Composer\Command;

use Composer\Config\JsonConfigSource;
use Composer\Factory;
use Composer\Json\JsonFile;
use SpipLeague\Composer\Config\PreferredInstall;
use SpipLeague\Composer\Factory as SpipComposerFactory;
use SpipLeague\Composer\SpipPaths;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'spip:set-ssh-url',
    description: 'The install method Composer will prefer to use',
    aliases: ['mode-dev'],
)]
/**
 * Turns remote https urls of source preferred-install packages into ssh urls
 * @since 0.6.0
 */
class ModeDevCommand extends AbstractSpipCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerFile = Factory::getComposerFile();
        $composer = $this->tryComposer();
        if ($composer === null) {
            return AbstractSpipCommand::FAILURE;
        }

        $output->writeln('Looking into ' . $composerFile);
        if ($composerFile == SpipPaths::LOCAL_COMPOSER) {
            $tmp = $composer->getConfig()
                ->get('preferred-install');
            if (is_string($tmp)) {
                $tmp = [$tmp];
            }
            if (!\is_array($tmp)) {
                $tmp = [];
            }
            if (empty(\array_intersect(['spip/*' => 'source'], $tmp))) {
                $output->writeln('<warning>Missing preferred-install in ' . $composerFile . '</warning>');
                if (!$this->getIO()->askConfirmation(
                    '<info>Adding default preferred-install ?</info> [<comment>Y,n</comment>]?',
                )) {
                    $output->writeln('Aborting.');
                    return AbstractSpipCommand::FAILURE;
                }
                $output->writeln('<info>Putting default preferred-install in ' . $composerFile . '</info>');
                $file = new JsonFile($composerFile);
                $json = new JsonConfigSource($file);
                $json->addConfigSetting('preferred-install', ['spip/*' => 'source']);
                $this->resetComposer();
                $composer = $this->tryComposer();
                if ($composer === null) {
                    return AbstractSpipCommand::FAILURE;
                }
            }
        }

        $myPreferredInstall = new PreferredInstall($composer);
        $toChange = $myPreferredInstall->getFromConfig();

        $changer = SpipComposerFactory::createRemoteUrls($composer);
        $output->writeln('Looking in git urls ...');
        foreach ($toChange as $path) {
            $url = '';
            $changer->getProcessor()?->execute($changer->getRemote($path), $url);
            $url = trim($url);
            if ($output->isVerbose()) {
                $output->write('Checking ' . $url . ' in ' . $path . ' ...');
            }
            $newUrl = $changer->toSsh($url);
            if ($newUrl !== $url) {
                $output->write(PHP_EOL . 'Converting ' . $url . ' into ' . $newUrl);
                $changer->getProcessor()?->execute($changer->setRemote($path, $newUrl));
            }
            if ($output->isVerbose()) {
                $output->writeln(' OK');
            }
        }
        $output->writeln(PHP_EOL . 'All done.');

        return AbstractSpipCommand::SUCCESS;
    }
}
