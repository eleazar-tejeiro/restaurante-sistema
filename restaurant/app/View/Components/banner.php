<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class banner extends Component
{
    /**
     * Create a new component instance.
     */   
    public $width;
    public $height;

    public function __construct( $width = 24, $height = 24)
    {
        $this->width = $width;
        $this->height = $height;
    }
  

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.banner');
    }
}