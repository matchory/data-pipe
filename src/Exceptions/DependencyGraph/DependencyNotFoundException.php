<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Exceptions\DependencyGraph;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;

class DependencyNotFoundException extends InvalidArgumentException
{
    protected PipelineNodeInterface $dependency;

    protected PipelineNodeInterface $requiredBy;

    #[Pure]
    public function __construct(
        PipelineNodeInterface $dependency,
        PipelineNodeInterface $requiredBy
    ) {
        $this->dependency = $dependency;
        $this->requiredBy = $requiredBy;

        parent::__construct(sprintf(
            "Dependency '%s' not found, required by '%s'",
            (string)$dependency,
            (string)$requiredBy
        ));
    }

    #[Pure]
    public function getDependency(): PipelineNodeInterface
    {
        return $this->dependency;
    }

    #[Pure]
    public function getRequiredBy(): PipelineNodeInterface
    {
        return $this->requiredBy;
    }
}
