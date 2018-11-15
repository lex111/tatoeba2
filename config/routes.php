<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\InflectedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 * Cache: Routes are cached to improve performance, check the RoutingMiddleware
 * constructor in your `src/Application.php` file to change this behavior.
 *
 */
Router::defaultRouteClass(InflectedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    // Array that lists all the languages into which the Tatoeba interface
    // has been translated
    $configUiLanguages = Configure::read('UI.languages');
    $iso3LangArray = array();
    foreach ($configUiLanguages as $lang) {
        $iso3LangArray[] = $lang[0];
        if (isset($lang[3]) && is_array($lang[3])) {
            foreach ($lang[3] as $alias) {
                $iso3LangArray[] = $alias;
            }
        }
    }
    $interfaceLanguages = join('|', $iso3LangArray);

    /**
     * To route tools, in order to still have tools in the URL, which is
     * clearer for users IMHO
     * this rule appears first, that way /fre/tools/search_Sinograms  is
     * not catch by the general rule for controllers
     */
    $routes->connect(
        '/tools/search_hanzi_kanji',
        [
            'controller' => 'Sinograms',
            'action' =>'index'
        ]
    );
    $routes->connect(
        '/tools/search_hanzi_kanji/:action',
        [
            'controller' => 'Sinograms',
        ]
    );

    $routes->connect(
        '/:lang/tools/search_hanzi_kanji',
        [
            'lang'=>'eng',
            'controller' => 'Sinograms',
            'action' =>'index'
        ]
    )
    ->setPatterns(['lang' => $interfaceLanguages])
    ->setPersist(['lang']);

    $routes->connect(
        '/:lang/tools/search_hanzi_kanji/:action',
        [
            'lang'=>'eng',
            'controller' => 'Sinograms',
        ]
    )
    ->setPatterns(['lang' => $interfaceLanguages])
    ->setPersist(['lang']);

    /**
     * Here, we are connecting '/' (base path) to a controller called 'Pages',
     * its action called 'display', and we pass a param to select the view file
     * to use (in this case, src/Template/Pages/home.ctp)...
     */
    $routes->connect(
        '/',
        [
            'controller' => 'Pages',
            'action' => 'index'
        ]
    );
    $routes->connect(
        '/:lang',
        [
            'lang' => ':lang',
            'controller' => 'Pages',
            'action' => 'index'
        ]
    )
    ->setPatterns(['lang' => $interfaceLanguages])
    ->setPersist(['lang']);

    $routes->connect(
        '/:action',
        [
            'controller' => 'pages',
        ]
    );
    $routes->connect(
        '/:lang/:action',
        [
            'lang' => ':lang',
            'controller' => 'pages',
        ]
    )
    ->setPatterns(['lang' => $interfaceLanguages])
    ->setPersist(['lang']);

    /**
     * La langue choisie sera maintenant disponible dans les contrôleurs
     * par la variable $this->params['lang'].
     */
    $routes->connect(
        '/:lang/:controller/:action/*',
        [
            'lang'=>'eng'
        ]
    )
    ->setPatterns(['lang' => $interfaceLanguages])
    ->setPersist(['lang']);

    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks(InflectedRoute::class);
});
