<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;

class FieldConfig
{
    private string $fieldId;
    private ?string $title;
    private ?bool $isEditable;
    private ?bool $isVisible;

    public function __construct(string $fieldId, ?string $title, ?bool $isEditable, ?bool $isVisible)
    {
        $this->fieldId = $fieldId;
        $this->title = $title;
        $this->isEditable = $isEditable;
        $this->isVisible = $isVisible;
    }

    public function getFieldId(): string
    {
        return $this->fieldId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getIsEditable(): ?bool
    {
        return $this->isEditable;
    }

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }
}
