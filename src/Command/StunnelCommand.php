<?php

namespace TorfsICT\StunnelBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class StunnelCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('stunnel')
            ->setDescription('Starts stunnel')
            ->setHelp('This command allows you to run stunnel and use HTTPS in your local development environment.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get required services
        $container = $this->getContainer();
        $twig = $this->getContainer()->get('templating');
        $kernel = $this->getContainer()->get('kernel');
        /** @var ProcessHelper $processHelper */
        $processHelper = $this->getHelper('process');

        // Process configuration
        $config = $this->getContainer()->getParameter('stunnel.config');
        if ($config['nanobox'] === true) {
            $failure = false;
            // Get forward_host from Nanobox
            $process = new Process('nanobox info local', $container->getParameter('kernel.project_dir'));
            $status = $processHelper->run($output, $process, 'Unable to retrieve Nanobox configuration.',
                function($type, $data) use (&$config, &$failure) {
                    if ($type !== Process::OUT) return;
                    $ret = preg_match('/^Env IP: (.*)$/m', $data, $matches);
                    if ($ret === 1) {
                        $config['forward_host'] = $matches[1];
                    } else {
                        throw new LogicException('Unable to retrieve IP address from Nanobox');
                    }
                }
            );
            if ($status->getExitCode() !== 0) return;
            // Get DNS aliases from Nanobox
            $process = new Process('nanobox dns ls local', $container->getParameter('kernel.project_dir'));
            $status = $processHelper->run($output, $process, 'Unable to retrieve Nanobox DNS aliases.',
                function($type, $data) use (&$config, $output) {
                    if ($type !== Process::OUT) return;
                    $data = array_filter(explode("\n", $data), function($item) {
                        return !empty($item) && $item !== 'DNS Aliases';
                    });
                    $alias = trim(array_pop($data));
                    if (count($data > 0)) {
                        $output->writeln(sprintf('Found multiple DNS aliases, using %s.', $alias));
                    }
                    $config['accept_host'] = $alias;
                }
            );
            if ($status->getExitCode() !== 0) return;
        } elseif (empty($config['forward_host']) || empty($config['accept_host'])) {
            throw new LogicException('The accept and forward hosts must be set if not extracted from Nanobox.');
        }
        $config['fullchain'] = realpath($config['fullchain']);
        $config['privkey'] = realpath($config['privkey']);

        // Write the stunnel configuration file
        $bindir = $kernel->locateResource('@TorfsICTStunnelBundle/bin');
        file_put_contents("$bindir/stunnel.conf",
            $twig->render('@TorfsICTStunnel/stunnel.conf.twig', $config)
        );

        // Get OS-native paths for both the executable and config file
        $conf = realpath($kernel->locateResource('@TorfsICTStunnelBundle/bin/stunnel.conf'));
        $exe = realpath($kernel->locateResource('@TorfsICTStunnelBundle/bin/stunnel.exe'));

        // Run our command
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $processHelper->run($output, ['start', '/B', $exe, $conf], 'Unable to start stunnel.');
        } else {
            if (posix_getuid() !== 0) {
                throw new LogicException('Root access is required to start stunnel (try sudo).');
            } else {
                $processHelper->run($output, ['stunnel', $conf], 'Unable to start stunnel.');
            }
        }
    }
}