<?php

namespace App\Twig;


use Twig\Attribute\AsTwigFunction;
use function Symfony\Component\String\u;

class IconRender
{
    public function __construct(private readonly string $projectDir)
    {
    }

    #[AsTwigFunction('lucide_icon', isSafe: ['html'])]
    public function setIcon(string $icon): string
    {
        $fileName = $this->projectDir . '/node_modules/lucide-static/icons/' . $this->stringToKebabCase($icon) . '.svg';
        return file_get_contents($fileName);
    }

    private function stringToKebabCase(string $string)
    {
        return u($string)->snake()->replace('_', '-');
    }
}
