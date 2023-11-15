<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Functions;

use LongitudeOne\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

/**
 * https://dev.mysql.com/doc/refman/8.2/en/spatial-relation-functions-object-shapes.html#function_st-distance
 */
class StDistance extends AbstractSpatialDQLFunction
{
    protected function getFunctionName(): string
    {
        return 'ST_Distance';
    }

    protected function getMaxParameter(): int
    {
        return 3;
    }

    protected function getMinParameter(): int
    {
        return 2;
    }

    protected function getPlatforms(): array
    {
        return ['mysql'];
    }
}
