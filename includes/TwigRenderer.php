<?php

namespace MyWeatherWidget;

class TwigRenderer
{
    private \Twig\Environment $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(plugin_dir_path(__FILE__) . '../view');
        $this->twig = new \Twig\Environment($loader);
    }

    public function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }
}
