<?php

namespace HtmlBuilder\Layouts;

use HtmlBuilder\Element;

class Tabs extends Element
{
    public function __construct()
    {
        parent::__construct('tabs');
    }
    
    public function tab(string $name, Element $element, $visible=false): self
    {
        $tab = (new static())->add($element);
        $tab->name = $name;
        $tab->visible = $visible;
        $this->add($tab);
        return $this;
    }
}