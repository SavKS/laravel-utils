<?php

namespace Savks\LaravelUtils\Support;

use Carbon\Carbon;
use Savks\LaravelUtils\Utils;

class Measuring
{
    /**
     * @var Carbon
     */
    protected $startsAt;

    /**
     * @var Carbon|null
     */
    protected $stoppedAt;

    /**
     * Measuring constructor.
     */
    public function __construct()
    {
        $this->startsAt = now();
    }

    /**
     * @return $this
     */
    public function stop(): Measuring
    {
        $this->stoppedAt = now();

        return $this;
    }

    /**
     * @param bool $pretty
     * @return string
     */
    public function executionTime(bool $pretty = true): string
    {
        $diff = $this->stoppedAt->diffInMilliseconds($this->startsAt);

        return $pretty ? Utils::humanReadableTime($diff) : $diff;
    }

    /**
     * @param bool $real
     * @param bool $pretty
     * @return int|string
     */
    public function showMemoryUsed(bool $real = true, bool $pretty = true)
    {
        if (! $pretty) {
            return memory_get_usage($real);
        }

        return Utils::humanReadableSize(
            memory_get_usage($real)
        );
    }

    /**
     * @param bool $real
     * @param bool $pretty
     * @return int|string
     */
    public function showMemoryPeakUsed(bool $real = true, bool $pretty = true)
    {
        if (! $pretty) {
            return memory_get_peak_usage($real);
        }

        return Utils::humanReadableSize(
            memory_get_peak_usage($real)
        );
    }
}
