<?php
/**
 * Part of Omega CMS - Renderer Package
 *
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
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
use function array_merge;
use function debug_backtrace;
use function extract;
use function ob_end_clean;
use function ob_get_contents;
use function realpath;
use Exception;
use Omega\Helpers\Alias;
use Omega\View\View;

/**
 * PhpRenderer class.
 *
 * @category    Omega
 * @package     Omega\Renderer
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class PhpRenderer implements RendererInterface
{
    use HasManagerTrait;

    protected array $layouts = [];

    /**
     * @inheritdoc
     *
     * @param  View $view Holds an instance of View.
     * @return string Return the view.
     */
    public function render( View $view ) : string
    {
        extract( $view->data );

        ob_start();
        include( $view->path );
        $contents = ob_get_contents();
        ob_end_clean();

        if ( $layout = $this->layouts[ $view->path ] ?? null ) {
            return Alias::view( $layout, array_merge(
                $view->data,
                [ 'contents' => $contents ],
            ) );
        }

        return $contents;
    }

    /**
     * Extends the template. 
     * 
     * This method extends the current template with another layout template.
     *
     * @param  string $template Holds the template name.
     * @return $this
     */
    protected function extends( string $template ) : static
    {
        $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 1 );
        $this->layouts[ realpath( $backtrace[ 0 ][ 'file' ] ) ] = $template;

        return $this;
    }

    /**
     * Magic call.
     * 
     * This method handles dynamic method calls, typically for macros.
     *
     * @param  string $name      Holds the method name. 
     * @param  mixed  ...$values Holds the method params/values.
     * @return mixed
     * @throws Exception
     */
    public function __call( string $name, mixed $values )
    {
        return $this->viewManager->useMacro( $name, ...$values );
    }
}