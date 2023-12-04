<?php
/**
 * Part of Omega CMS - Renderer Package
 *
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/**
 * @declare
 */
//declare( strict_types = 1 );

/**
 * @namespace
 */
namespace Omega\Renderer;

/**
 * @use
 */
use function file_get_contents;
use Omega\View\View;

/**
 * Literal renderer class.
 * 
 * This class is part of the Omega CMS Renderer Package and provides a renderer 
 * that treats the view as a literal file. It directly returns the contents of 
 * the view file without any processing. 
 *
 * @category    Omega
 * @package     Omega\Renderer
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class LiteralRenderer implements RendererInterface
{
    use HasManagerTrait;

    /**
     * @inheritdoc
     *
     * @param  View $view Holds an instance of View.
     * @return string Return the view.
     */
    public function render( View $view ) : string
    {
        return file_get_contents( $view->path );
    }
}
