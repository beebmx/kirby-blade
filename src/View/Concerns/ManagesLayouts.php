<?php

namespace Beebmx\View\Concerns;

use Illuminate\View\Concerns\ManagesLayouts as Layouts;

trait ManagesLayouts
{
    use Layouts {
        Layouts::startSection as parentStartSection;
        Layouts::yieldContent as parentYieldContent;
    }

    /**
     * Start injecting content into a section.
     *
     * @param  string  $section
     * @param  string|null  $content
     */
    public function startSection($section, $content = null): void
    {
        if ($content === null) {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content instanceof View ? $content : b($content));
        }
    }

    /**
     * Get the string contents of a section.
     *
     * @param  string  $section
     * @param  string  $default
     */
    public function yieldContent($section, $default = ''): string
    {
        $sectionContent = $default instanceof View ? $default : b($default);
        if (isset($this->sections[$section])) {
            $sectionContent = $this->sections[$section];
        }
        $sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);

        return str_replace(
            '--parent--holder--',
            '@parent',
            str_replace(static::parentPlaceholder($section), '', $sectionContent)
        );
    }
}
