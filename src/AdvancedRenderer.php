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
use function array_merge;
use function debug_backtrace;
use function extract;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function is_file;
use function md5;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function preg_replace_callback;
use function realpath;
use function touch;
use Exception;
use Omega\Helpers\Alias;
use Omega\View\View;

/**
 * Advanced renderer class. 
 * 
 * The `AdvancedRenderer` class is part of the Omega CMS Renderer Package and 
 * provides advanced rendering capabilities.
 *
 * @category    Omega
 * @package     Omega\Renderer
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class AdvancedRenderer implements RendererInterface
{
    use HasManagerTrait;

    /**
     * Layout array.
     *
     * @var array $layouts Holds an array of layouts.
     */
    protected array $layouts = [];

    /**
     * @inheritdoc
     *
     * @param  View $view Holds an instance of View.
     * @return string Return the view.
     */
    public function render( View $view ) : string
    {
        $hash     = md5( $view->path );
        $basePath = realpath(__DIR__ . '/../../../..');
        $folder   = $basePath . '/storage/views';

        if ( ! is_file( "{$folder}/{$hash}.php" ) ) {
            touch( "{$folder}/{$hash}.php" );
        }

        $cached = realpath( "{$folder}/{$hash}.php" );

        if ( ! file_exists( $hash ) || filemtime( $view->path ) > filemtime( $hash ) ) {
            $content = $this->compile( file_get_contents( $view->path ) );
            file_put_contents( $cached, $content );
        }

        extract( $view->data );

        ob_start();
        include( $cached );
        $contents = ob_get_contents();
        ob_end_clean();

        if ( $layout = $this->layouts[ $cached ] ?? null ) {
            return Alias::view( $layout, array_merge(
                $view->data,
                [ 'contents' => $contents ],
            ) );
        }

        return $contents;
    }

    /**
     * Compile the template. 
     * 
     * This method compiles the template content by processing various directives 
     * and constructs.
     *
     * @param  string $template Holds the template name.
     * @return string Return the compiled template.
     */
    protected function compile( string $template ) : string
    {
        // replace `@extends` with `$this->extends`
        $template = preg_replace_callback( '#@extends\(((?<=\().*(?=\)))\)#', function ( $matches ) {
            return '<?php $this->extends(' . $matches[ 1 ] . '); ?>';
        }, $template );

        // replace `@if` with `if(...):`
        $template = preg_replace_callback( '#@if\(((?<=\().*(?=\)))\)#', function ( $matches ) {
            return '<?php if(' . $matches[ 1 ] . '): ?>';
        }, $template );

        $template = preg_replace_callback( '#@else#', function ( $matches ) {
            return '<?php else: ?>';
        }, $template );

        $template = preg_replace_callback( '#@elseif\(((?<=\().*(?=\)))\)#', function ( $matches ) {
            return '<?php elseif(' . $matches[ 1 ] . '): ?>';
        }, $template );

        // replace `@endif` with `endif;`
        $template = preg_replace_callback( '#@endif#', function ( $matches ) {
            return '<?php endif; ?>';
        }, $template );

        // replace `@foreach` with `foreach(...):`
        $template = preg_replace_callback( '#@foreach\(((?<=\().*(?=\)))\)#', function ( $matches ) {
            return '<?php foreach(' . $matches[ 1 ] . '): ?>';
        }, $template );

        // replace `@endforeach` with `endforeach;`
        $template = preg_replace_callback( '#@endforeach#', function ( $matches ) {
            return '<?php endforeach; ?>';
        }, $template );

        // replace `@[anything](...)` with `$this->[anything](...)`
        $template = preg_replace_callback( '#\s+@([^(]+)\(((?<=\().*(?=\)))\)#', function ( $matches ) {
            return '<?php $this->' . $matches[ 1 ] . '(' . $matches[ 2 ] . '); ?>';
        }, $template );

        // replace `{{ ... }}` with `print $this->escape(...)`
        $template = preg_replace_callback( '#\{\{([^}]*)\}\}#', function ( $matches ) {
            return '<?php print $this->escape(' . $matches[ 1 ] . '); ?>';
        }, $template );

        // replace `{!! ... !!}` with `print ...`
        $template = preg_replace_callback( '#\{!!([^}]+)!!\}#', function ( $matches ) {
            return '<?php print ' . $matches[ 1 ] . '; ?>';
        }, $template );

        return $template;
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
    public function __call( string $name, mixed $values ) : mixed
    {
        return $this->viewManager->useMacro( $name, ...$values );
    }
}
