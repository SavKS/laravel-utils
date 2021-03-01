<?php

namespace Savks\LaravelUtils;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Savks\LaravelUtils\Support\Measuring;
use Symfony\Component\Process\Process;

class Utils
{
    /**
     * @param callable|null $callback
     * @return Measuring|null
     */
    public static function startMeasuring(callable $callback = null): ?Measuring
    {
        $measuring = new Measuring();

        if ($callback) {
            $result = $callback($measuring);

            return $result !== false ? $measuring->stop() : null;
        }

        return $measuring;
    }

    /**
     * @param string $command
     * @param array $args
     * @param int $timeout
     * @return array
     */
    public static function runCommandInBackground(string $command, array $args = [], int $timeout = 60): array
    {
        if (class_exists($command)) {
            $command = (new $command())->getName();
        }

        $process = new Process(
            array_merge(
                [
                    'php',
                    base_path('artisan'),
                    $command,
                ],
                $args
            )
        );

        $process->setTimeout($timeout);

        $process->run();

        return [
            $process->isSuccessful(),
            $process->getOutput(),
            $process->getErrorOutput(),
        ];
    }

    /**
     * @param array $pairs
     * @return array
     */
    public static function combinations(array $pairs): array
    {
        if (empty($pairs)) {
            return $pairs;
        }

        if (count($pairs) === 1) {
            return array_map(
                function ($value) {
                    return [$value];
                },
                head($pairs)
            );
        }

        $pair = array_shift($pairs);

        $state = static::combinations($pairs);

        $newState = [];

        foreach ($pair as $item) {
            foreach ($state as $combination) {
                $newState[] = array_merge($combination, [$item]);
            }
        }

        return $newState;
    }

    /**
     * @param float $bytes
     * @param int $decimals
     * @return string
     */
    public static function humanReadableSize(float $bytes, int $decimals = 2): string
    {
        $size = ['Bytes', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];

        $factor = floor(
            (
                strlen((string)$bytes) - 1
            ) / 3
        );

        return sprintf(
            "%.{$decimals}f %s",
            $bytes / pow(1024, $factor),
            @$size[$factor]
        );
    }

    /**
     * @param int $ms
     * @return string
     */
    public static function humanReadableTime(int $ms): string
    {
        $xx = $ms < 0 ? "-" : "+";
        $ms = abs($ms);
        $ss = floor($ms / 1000);
        $ms = $ms % 1000;
        $mm = floor($ss / 60);
        $ss = $ss % 60;
        $hh = floor($mm / 60);
        $mm = $mm % 60;
        $dd = floor($hh / 24);
        $hh = $hh % 24;

        return sprintf("%s%dd %02dh %02dm %02d.%04ds", $xx, $dd, $hh, $mm, $ss, $ms);
    }

    /**
     * @param string|QueryBuilder|EloquentBuilder|Relation $query
     * @param array $params
     * @return string
     */
    public static function interpolateQuery($query, array $params = []): string
    {
        if (! is_string($query)) {
            $params = $query->getBindings();
            $query = $query->toSql();
        }

        $result = '';

        $parts = explode('?', $query);

        if (count($parts) - 1 !== count($params)) {
            throw new LogicException('Bind count values and placeholders not match');
        }

        foreach ($parts as $index => $part) {
            if (! $params) {
                $result .= $part;

                break;
            }

            $param = array_shift($params);

            $result .= $part . (is_numeric($param) ? $param : "'{$param}'");
        }

        return $result;
    }
}
