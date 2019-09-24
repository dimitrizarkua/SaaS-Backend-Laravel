<?php

namespace App\Logging;

use Monolog\Logger;

/**
 * Class DataDogFormatter
 *
 * @package App\Logging
 */
class DataDogFormatter
{
    const DATA_DOG_DEBUG_STATUS   = 'debug';
    const DATA_DOG_INFO_STATUS    = 'info';
    const DATA_DOG_WARNING_STATUS = 'warning';
    const DATA_DOG_ERROR_STATUS   = 'error';

    /**
     * Map Monolog\Logger logging levels to Datadog alert_type
     */
    const ALERT_TYPE_MAP = [
        Logger::DEBUG     => self::DATA_DOG_DEBUG_STATUS,
        Logger::INFO      => self::DATA_DOG_INFO_STATUS,
        Logger::NOTICE    => self::DATA_DOG_WARNING_STATUS,
        Logger::WARNING   => self::DATA_DOG_WARNING_STATUS,
        Logger::ERROR     => self::DATA_DOG_ERROR_STATUS,
        Logger::ALERT     => self::DATA_DOG_ERROR_STATUS,
        Logger::CRITICAL  => self::DATA_DOG_ERROR_STATUS,
        Logger::EMERGENCY => self::DATA_DOG_ERROR_STATUS,
    ];

    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger $logger
     *
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function ($record) {
                if (isset($record['context']['reference_id'])) {
                    $record['reference_id'] = $record['context']['reference_id'];
                    unset($record['context']['reference_id']);
                }
                $record['status'] = isset(self::ALERT_TYPE_MAP[$record['level']])
                    ? self::ALERT_TYPE_MAP[$record['level']]
                    : self::DATA_DOG_INFO_STATUS;

                return $record;
            });
        }
    }
}
