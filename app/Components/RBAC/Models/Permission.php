<?php

namespace App\Components\RBAC\Models;

/**
 * Class Permission
 *
 * @package App\Components\RBAC\Models
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name","display_name","description"}
 * )
 */
class Permission
{
    /**
     * Permission name.
     *
     * @OA\Property(
     *     description="Permission name",
     *     example="users.create"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * Permission display name.
     *
     * @OA\Property(
     *     description="Display name",
     *     example="Create user"
     * )
     *
     * @var string
     */
    private $displayName;

    /**
     * Permissions description.
     *
     * @OA\Property(
     *     description="Permission description",
     *     example="Allows to create new users"
     * )
     *
     * @var string
     */
    private $description;

    /**
     * Specific rule for the permission.
     * This callback function will accept three arguments:
     * - User $user - authenticated user model from which endpoint is being calling
     * - array $arguments - any argument passed from $this->authenticate($ability, $arguments) method called from
     *                      controllers action. In most cases it will be a model that user is trying to modify.
     *
     * @var callable
     */
    private $rule;

    /**
     * Permission constructor.
     *
     * @param $name
     * @param $displayName
     * @param $description
     * @param $rule
     */
    public function __construct(
        string $name,
        string $displayName = null,
        string $description = null,
        callable $rule = null
    ) {
        $this->name        = $name;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->rule        = $rule;
    }

    /**
     * Checks whether permission has rule.
     *
     * @return bool
     */
    public function hasRule(): bool
    {
        return null !== $this->rule;
    }

    /**
     * Return rule callback.
     *
     * @return callable
     */
    public function getRule(): ?callable
    {
        return $this->rule;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
