<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as BaseFormatter;

/**
 * Class JsonFormatter
 *
 * @package App\Logging
 */
class JsonFormatter extends BaseFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        if (isset($record['context']) && isset($record['context']['exception'])) {
            $record['context']['exception']['trace'] = substr($record['context']['exception']['trace'], 0, 4096);
        }

        return parent::format($record);
    }
}
