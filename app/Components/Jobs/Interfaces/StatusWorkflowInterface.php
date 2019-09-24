<?php

namespace App\Components\Jobs\Interfaces;

/**
 * Interface StatusWorkflowInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface StatusWorkflowInterface
{
    /**
     * Register new status.
     *
     * @param string $status
     *
     * @return \App\Components\Jobs\Interfaces\StatusWorkflowInterface
     */
    public function addStatus(string $status): self;

    /**
     * Get current status.
     *
     * @return string
     */
    public function getCurrentStatus(): string;

    /**
     * Get list of next available statuses.
     *
     * @return array
     */
    public function getNextStatuses(): array;

    /**
     * Change status. Optional parameters could be passed to manage additional actions.
     *
     * @param string      $status
     * @param string|null $note
     * @param int|null    $userId
     *
     * @return \App\Components\Jobs\Interfaces\StatusWorkflowInterface
     */
    public function changeStatus(string $status, string $note = null, int $userId = null): self;

    /**
     * Add available transition from one status to another.
     *
     * @param string $fromStatus
     * @param string $toStatus
     *
     * @return \App\Components\Jobs\Interfaces\StatusWorkflowInterface
     */
    public function addTransition(string $fromStatus, string $toStatus): self;

    /**
     * Add transitions from one status to many others.
     *
     * @param string $fromStatus
     * @param array  $toStatuses
     *
     * @return \App\Components\Jobs\Interfaces\StatusWorkflowInterface
     */
    public function addTransitions(string $fromStatus, array $toStatuses): self;
}
