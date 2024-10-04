<?php

namespace Phiki\Grammar;

use Phiki\Contracts\ContainsCapturesInterface;
use Phiki\Contracts\PatternCollectionInterface;
use Phiki\MatchedPattern;
use Phiki\Regex;
use Phiki\Tokenizer;

class BeginEndPattern extends Pattern implements ContainsCapturesInterface, PatternCollectionInterface
{
    public function __construct(
        public Regex $begin,
        public Regex $end,
        public ?string $name,
        public ?string $contentName,
        public array $beginCaptures = [],
        public array $endCaptures = [],
        public array $captures = [],
        public array $patterns = [],
        public bool $injection = false,
    ) {}

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    public function hasPatterns(): bool
    {
        return count($this->patterns) > 0;
    }

    public function tryMatch(Tokenizer $tokenizer, string $lineText, int $linePosition, ?int $cannotExceed = null): MatchedPattern|false
    {
        if (preg_match('/'.$this->begin->get().'/u', $lineText, $matches, PREG_OFFSET_CAPTURE, $linePosition) !== 1) {
            return false;
        }

        if ($cannotExceed !== null && $matches[0][1] > $cannotExceed) {
            return false;
        }

        return new MatchedPattern($this, $matches);
    }

    public function scope(): ?array
    {
        return $this->name ? explode(' ', $this->name) : null;
    }

    public function hasCaptures(): bool
    {
        return count($this->beginCaptures) > 0 || count($this->captures) > 0;
    }

    public function getCaptures(): array
    {
        return count($this->beginCaptures) > 0 ? $this->beginCaptures : $this->captures;
    }

    public function createEndPattern(MatchedPattern $self): EndPattern
    {
        return new EndPattern(
            $self,
            $this->end,
            $this->name,
            $this->contentName,
            $this->endCaptures,
            $this->captures,
            $this->patterns,
        );
    }
}
