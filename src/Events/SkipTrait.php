<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;

trait SkipTrait
{
    protected bool $skip = false;

    public function skip(bool $skip = true): self
    {
        $this->skip = $skip;

        return $this;
    }

    public function continue(bool $continue = true): self
    {
        $this->skip(! $continue);

        return $this;
    }

    #[Pure]
    public function shouldSkip(): bool
    {
        return $this->skip;
    }
}
