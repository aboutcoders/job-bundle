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
            ->addOption('max-iterations', null, InputOption::VALUE_OPTIONAL, 'Maximum time in seconds the consumer will run.', null)
            ->addArgument('queue', InputArgument::REQUIRED, 'Name of queue that will be consumed.');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->consumer->consume($input->getArgument('queue'), $input->getOptions());
    }
}