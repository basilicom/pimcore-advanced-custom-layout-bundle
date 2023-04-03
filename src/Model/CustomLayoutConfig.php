<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Model;

use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig\FieldConfig;

class CustomLayoutConfig
{
    public const MODE_SHOW = 'show-listed';
    public const MODE_EDIT = 'edit-listed';

    private string $className;
    private string $layoutIdentifier;
    private string $label;
    private string $mode;
    /** @var FieldConfig[] */
    private array $fields;
    /** @var string[] */
    private array $autoApplyForRoles;
    /** @var string[] */
    private array $autoApplyForWorkflowStates;

    /**
     * @param string        $className
     * @param string        $layoutIdentifier
     * @param string        $label
     * @param string        $mode
     * @param string[]      $autoApplyForRoles
     * @param string[]      $autoApplyForWorkflowStates
     * @param FieldConfig[] $fields
     */
    public function __construct(
        string $className,
        string $layoutIdentifier,
        string $label,
        string $mode,
        array $autoApplyForRoles,
        array $autoApplyForWorkflowStates,
        array $fields
    ) {
        $this->className = $className;
        $this->layoutIdentifier = $layoutIdentifier;
        $this->label = $label;
        $this->mode = $mode;
        $this->autoApplyForRoles = $autoApplyForRoles;
        $this->autoApplyForWorkflowStates = $autoApplyForWorkflowStates;
        $this->fields = $fields;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFullQualifiedClassName(): string
    {
        return '\\' . ltrim($this->className, '\\');
    }

    public function getLayoutIdentifier(): string
    {
        return $this->layoutIdentifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return string[]
     */
    public function getAutoApplyForRoles(): array
    {
        return $this->autoApplyForRoles;
    }

    /**
     * @return string[]
     */
    public function getAutoApplyForWorkflowStates(): array
    {
        return $this->autoApplyForWorkflowStates;
    }

    /**
     * @return FieldConfig[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
