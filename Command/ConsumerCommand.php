<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Command;

use Abc\Bundle\JobBundle\Job\Queue\ConsumerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class ConsumerCommand extends Command
{
    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @param ConsumerInterface $consumer
     */
    public function __construct(ConsumerInterface $consumer)
    {
        parent::__construct();
        $this->consumer = $consumer;
    }

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('abc:job:consume');
        $this->setDescription('Consume jobs from a queue');

        $this
            ->addArgument('queue', InputArgument::REQUIRED, 'Name of queue that will be consumed.')
            ->addOption('max-runtime', null, InputOption::VALUE_OPTIONAL, 'Maximum time in seconds the consumer will run.', null)
            ->addOption('max-messages', null, InputOption::VALUE_OPTIONAL, 'Maximum number of messages that should be consumed.', null)
            ->addOption('stop-when-empty', null, InputOption::VALUE_NONE, 'Stop consumer when queue is empty.', null);
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->consumer->consume($input->getArgument('queue'), [
            'max-runtime'     => $input->getOption('max-runtime'),
            'max-messages'    => $input->getOption('max-messages'),
            'stop-when-empty' => $input->getOption('stop-when-empty')
        ]);
    }
}