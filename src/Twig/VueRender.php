<?php

namespace App\Twig;

use Twig\Attribute\AsTwigFunction;

class VueRender
{
    #[AsTwigFunction('vue_component', isSafe: ['html'])]
    public function renderVueDataAttribute(string $component, ?array $props): string
    {

        $string = 'data-vue-component="' . $component . '"';
        if (!$props || count($props) == 0) {
            return $string;
        }

        return $string . ' data-vue-props="' . htmlspecialchars(json_encode($props), ENT_QUOTES) . '"';
    }
}
