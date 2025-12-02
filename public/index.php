<?php

use App\Kernel;

// Ignore warnings/dépréciations déclenchés par les ini_set d'assertions avant l'init du runtime.
$previousHandler = set_error_handler(static function (int $type) {
    if (\in_array($type, [E_WARNING, E_DEPRECATED, E_USER_DEPRECATED], true)) {
        return true;
    }

    return false;
});

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if ($previousHandler) {
    set_error_handler($previousHandler);
} else {
    restore_error_handler();
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
