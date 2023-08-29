<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;

class LayoutElementConfig
{
    private string $elementId;
    private ?string $title;
    private ?bool $isVisible;

    public function __construct(string $elementId, ?string $title, ?bool $isVisible)
    {
        $this->elementId = $elementId;
        $this->title = $title;
        $this->isVisible = $isVisible;
    }

    public function getElementId(): string
    {
        return $this->elementId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }
}
