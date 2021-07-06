<?php

/**
 * This file is part of matchory-pipeline, a Matchory application.
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Its contents are strictly confidential and proprietary.
 *
 * @copyright 2020–2021 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Symfony\Command;

use DusanKasan\Knapsack\Collection;
use Matchory\DataPipe\Exceptions\DependencyGraph\CircularDependencyException;
use Matchory\DataPipe\Exceptions\DependencyGraph\DependencyNotFoundException;
use Matchory\DataPipe\Interfaces\CollectorInterface as Collector;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface as Node;
use Matchory\DataPipe\Interfaces\TransformerInterface as Transformer;
use Matchory\DataPipe\PipelineRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugPipelineCommand extends Command
{
    public const NAME = 'debug:pipeline';

    public const OPTION_COLLECTORS = 'collectors';

    public const OPTION_COLLECTORS_SHORTHAND = 'c';

    public const OPTION_TRANSFORMERS = 'transformers';

    public const OPTION_TRANSFORMERS_SHORTHAND = 't';

    protected static $defaultName = self::NAME;

    public function __construct(private PipelineRegistry $registry)
    {
        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Resolve and dump the current pipeline');

        $this->addOption(
            self::OPTION_COLLECTORS,
            self::OPTION_COLLECTORS_SHORTHAND,
            InputOption::VALUE_NONE,
            'Only show collectors in the output',
        );

        $this->addOption(
            self::OPTION_TRANSFORMERS,
            self::OPTION_TRANSFORMERS_SHORTHAND,
            InputOption::VALUE_NONE,
            'Only show transformers in the output',
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws CircularDependencyException
     * @throws DependencyNotFoundException
     * @throws InvalidArgumentException
     * @noinspection NestedTernaryOperatorInspection
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $nodes = Collection::from($this->registry->resolvePipeline());

        $io = new SymfonyStyle($input, $output);
        $table = new Table($io);

        $onlyCollectors = $input->getOption(self::OPTION_COLLECTORS);
        $onlyTransformers = $input->getOption(self::OPTION_TRANSFORMERS);

        if ($onlyCollectors && $onlyTransformers) {
            $io->getErrorStyle()->error(
                '--collectors and --transformers are mutually exclusive'
            );

            return self::INVALID;
        }

        $table
            ->setHeaderTitle('Collectors')
            ->setHeaders([
                '#',
                'Node',
                'Type',
                'Cost',
                'Provides Fields',
                'Depends on',
            ])
            ->setRows($nodes
                ->filter(fn(Node $node) => (
                    ( ! $onlyCollectors && ! $onlyTransformers) ||
                    ($onlyCollectors && $node instanceof Collector) ||
                    ($onlyTransformers && $node instanceof Transformer)
                ))
                ->map(fn(Node $node, int $index): array => [
                    $index + 1,
                    $node::class,
                    $node instanceof Collector ? 'Collector' : 'Transformer',
                    $node instanceof Collector ? $node->getCost() : '--',
                    $node instanceof Collector
                        ? (implode(', ', $node->getProvidedFields()) ?: '--')
                        : '--',
                    implode(', ', $node->getDependencies()) ?: '--',
                ])
                ->toArray())
            ->render();

        return self::SUCCESS;
    }
}
