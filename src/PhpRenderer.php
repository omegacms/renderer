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
declare( strict_types = 1 );

/**
 * @namespace
 */
namespace Omega\Renderer;

/**
 * @use
 */
use function array_merge;
use function Omega\Helpers\view;
use Omega\View\View;

/**
 * Php renderer class.
 * 
 * @category    Omega
 * @package     Omega\Renderer
 * @link        https://omegacms.github.io1
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class PhpRenderer extends AbstractRenderer
{
    /**
     * @inheritdoc
     *
     * This method is responsible for rendering a View object and processing its contents.
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
            $contentsWithLayout = view( $layout, array_merge(
                $view->data,
                [ 'contents' => $contents ],
            ) );

            return $contentsWithLayout;
        }

        return $contents;
    }
}