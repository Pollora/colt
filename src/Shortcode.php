<?php

declare(strict_types=1);

namespace Pollora\Colt;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * Interface Shortcode
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
interface Shortcode
{
    /**
     * @return string
     */
    public function render(ShortcodeInterface $shortcode);
}
