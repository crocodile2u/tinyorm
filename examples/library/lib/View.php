<?php

namespace library;

class View {
    static function render($template, $vars = []) {
        extract($vars);
        ob_start();
        include __DIR__ . "/../view/" . $template;
        return ob_get_clean();
    }
}