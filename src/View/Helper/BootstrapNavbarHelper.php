<?php
/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 * You may obtain a copy of the License at
 *
 *     https://opensource.org/licenses/mit-license.php
 *
 *
 * @copyright Copyright (c) Mikaël Capelle (https://typename.fr)
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Bootstrap\View\Helper;

use Cake\View\Helper;
use Cake\Routing\Router;

class BootstrapNavbarHelper extends Helper {

    use BootstrapTrait;

    /**
     * Other helpers used by BootstrapNavbarHelper.
     *
     * @var array
     */
    public $helpers = [
        'Html',
        'Form' => [
            'className' => 'Bootstrap.BootstrapForm'
        ]
    ];

    /**
     * Default configuration for the helper.
     *
     * - `autoActiveLink` Set to `true` to automatically add `active` class
     * when given URL for a link matches the current URL. Default is `true`.
     *
     * @var array
     */
    public $_defaultConfig = [
        'autoActiveLink' => true
    ];

    /**
     * Indicates if navbar is responsive or not.
     *
     * @var bool
     */
    protected $_responsive = false;

    /**
     * Indicates if navbar is inside a container.
     *
     * @var bool
     */
    protected $_container = false;

    /**
     * Menu level (0 = out of menu, 1 = main horizontal menu, 2 = dropdown menu).
     *
     * @var int
     */
    protected $_level = 0;

    /**
     * Create a new navbar.
     *
     * ### Options:
     * - `container` Wrap navbar inside a container. Default is `false`.
     * - `fixed` Fixed navbar, possible values are `'top'`, `'bottom'`, `false`. Default
     * is `false`.
     * - `innerId` HTML id for the inner div (only used for responsive navbar).
     * - `inverse` Inverted navbar. Default is `false`.
     * - `responsive` Responsive navbar. Default is `true`.
     * - `sticky` Sticky navbar. Default is `false`.
     *
     * @param string $brand   Brand name.
     * @param array  $options Array of options. See above.
     *
     * @return A string containing the HTML starting element of the navbar.
     */
    public function create($brand, $options = []) {

        // TODO: More options for container?
        // TODO: More options for bg control
        // TODO: MOre controls on responsive (toggeable-md, ...).

        $options += [
            'innerId' => 'navbarSupportedContent',
            'fixed' => false,
            'responsive' => true,
            'sticky' => false,
            'inverse' => false,
            'container' => false
        ];

        $fixed = $options['fixed'];
        $sticky = $options['sticky'];
        $inverse = $options['inverse'];
        $innerId = $options['innerId'];
        $this->_responsive = $options['responsive'];
        $this->_container = $options['container'];
        unset($options['fixed'], $options['responsive'],
              $options['container'], $options['sticky'],
              $options['inverse'], $options['innerId']);

        /** Generate options for outer div. **/
        $options = $this->addClass($options, 'navbar');
        if ($inverse) {
            $options = $this->addClass($options , 'navbar-inverse bg-inverse');
        }
        else {
            $options = $this->addClass($options , 'navbar-light bg-faded');
        }
        if ($fixed !== false) {
            $options = $this->addClass($options, 'fixed-'.$fixed);
        }
        else if ($sticky !== false) {
            $options = $this->addClass($options, 'sticky-top');
        }

        if ($brand) {
            if (is_string($brand)) {
                $brand = $this->Html->link ($brand, '/', [
                    'class' => 'navbar-brand',
                    'escape' => false
                ]);
            }
            else if (is_array($brand) && array_key_exists('url', $brand)) {
                $brand += [
                    'options' => []
                ];
                $brand['options'] = $this->addClass ($brand['options'], 'navbar-brand');
                $brand = $this->Html->link ($brand['name'], $brand['url'], $brand['options']);
            }
        }

        $toggleButton = '';
        if ($this->_responsive) {
            $icon = $this->Html->tag('span', '', ['class' => 'navbar-toggle-icon']);
            $toggleButton = $this->Html->tag('button', $icon, [
                'type' => 'button',
                'class' => 'navbar-toggler navbar-toggler-right',
                'data-toggle' => 'collapse',
                'data-target' => '#'.$innerId,
                'aria-controls' => $innerId,
                'aria-expanded' => 'false',
                'aria-label' => __('Toggle navigation')
            ]);
            $options = $this->addClass($options, 'navbar-toggleable-md');
        }

        $out = $this->Html->tag('nav', null, $options).$toggleButton.$brand;

        if ($this->_responsive) {
            $out .= $this->Html->tag('div', null, [
                'class' => 'collapse navbar-collapse',
                'id' => $innerId
            ]);
        }

        if ($this->_container) {
            $out = $this->Html->tag('div', null, ['class' => 'container']).$out;
        }

        return $out;
    }

    /**
     * Add a link to the navbar or to a menu.
     *
     * Links outside a menu are realized as buttons. Encapsulate links with
     * `beginMenu()`, `endMenu()` to create a horizontal hover menu in the navbar.
     *
     * @param string       $name        The link text.
     * @param string|array $url         The link URL (sent to `Html::link` method).
     * @param array        $options     Array of options for the `<li>` tag.
     * @param array        $linkOptions Array of options for the `Html::link` method.
     *
     * @return string A HTML `<li>` tag wrapping the link.
     */
    public function link($name, $url = '', array $options = [], array $linkOptions = []) {
        if (Router::url() == Router::url($url) && $this->config('autoActiveLink')) {
            $options = $this->addClass($options, 'active');
        }
        if ($this->_level == 1) {
            $options = $this->addClass($options, 'nav-item');
            $linkOptions = $this->addClass($linkOptions, 'nav-link');
            return $this->Html->tag('li', $this->Html->link($name, $url, $linkOptions),
                                    $options);
        }
        else if ($this->_level == 2) {
            $options = $this->addClass($options, 'dropdown-item');
            return $this->Html->link($name, $url, $options);
        }
        // TODO: Throw an exception?
        return '';
    }

    /**
     * Add a button to the navbar.
     *
     * @param string $name    Text of the button.
     * @param array  $options Options sent to the `Form::button` method.
     *
     * @return string A HTML navbar button.
     */
    public function button($name, array $options = []) {
        return $this->Form->button($name, $options);
    }

    /**
     * Add a divider to the navbar or to a menu.
     *
     * @param array $options Array of options for the `<li>` tag.
     *
     * @return A HTML divider `<li>` tag.
     */
    public function divider(array $options = []) {
        $options = $this->addClass ($options, 'dropdown-divider');
        return $this->Html->tag('div', '', $options);
    }

    /**
     * Add a header to the navbar or to a menu, should not be used outside a submenu.
     *
     * @param string $name    Title of the header.
     * @param array  $options Array of options for the `<li>` tag.
     *
     * @return A HTML header `<li>` tag.
     */
    public function header($name, array $options = []) {
        $options = $this->addClass($options, 'dropdown-header');
        return $this->Html->tag('h6', $name, $options);
    }

    /**
     * Add a text to the navbar.
     *
     * ### Options:
     *
     * - `tag` The HTML tag used to wrap the text. Default is `'span'`.
     * - Other attributes will be assigned to the wrapper element.
     *
     * @param string $text The text message.
     * @param array  $options Array of options. See above.
     *
     * @return string A HTML element wrapping the text for the navbar.
     */
    public function text($text, $options = []) {
        $options += [
            'tag' => 'span'
        ];
        $tag = $options['tag'];
        unset($options['tag']);
        $options = $this->addClass($options, 'navbar-text');
        return $this->Html->tag($tag, $text, $options);
    }


    /**
     * Add a serach form to the navbar.
     *
     * ### Options:
     *
     * - `align` Search form alignment. Default is `'left'`.
     * - Other options will be passed to the `Form::searchForm` method.
     *
     * @param mixed $model   Model for BootstrapFormHelper::searchForm method.
     * @param array $options Array of options. See above.
     *
     * @return string An HTML search form for the navbar.
     */
    public function searchForm($model = null, $options = []) {
        $options += [
            'align' => 'left'
        ];
        $options = $this->addClass($options, ['navbar-form',  'navbar-'.$options['align']]);
        unset ($options['align']);
        return $this->Form->searchForm($model, $options);
    }

    /**
     * Start a new menu.
     *
     * Two types of menus exist:
     * - Horizontal hover menu in the navbar (level 0).
     * - Vertical dropdown menu (level 1).
     * The menu level is determined automatically: A dropdown menu needs to be part of
     * a hover menu. In the hover menu case, pass the options array as the first argument.
     *
     * You can populate the menu with `link()`, `divider()`, and sub menus.
     * Use `'class' => 'navbar-right'` option for flush right.
     *
     * **Note:** The `$linkOptions` and `$listOptions` parameters are not used for menu
     * at level 0 (horizontal menu).
     *
     * ### Link Options:
     *
     * - `aria-expanded` HTML attribute. Default is `'false'`.
     * - `aria-haspopup` HTML attribute. Default is `'true'`.
     * - `data-toggle` HTML attribute. Default is `'dropdown'`.
     * - `escape` CakePHP option. Default is `false`.
     *
     * @param string       $name        Name of the menu.
     * @param string|array $url         URL for the menu.
     * @param array        $options     Array of options for the wrapping `<li>` element.
     * @param array        $linkOptions Array of options for the link. See above.
     * element (`Html::link` method).
     * @param array        $listOptions Array of options for the openning `ul` elements.
     *
     * @return string HTML elements to start a menu.
     */
    public function beginMenu($name = null, $url = null, $options = [],
                              $linkOptions = [], $listOptions = []) {
        $res = '';
        if ($this->_level == 0) {
            $options = is_array($name) ? $name : [];
            $options = $this->addClass($options, 'navbar-nav');
            $res = $this->Html->tag('ul', null, $options);
        }
        else {
            $linkOptions += [
                'data-toggle' => 'dropdown',
                'aria-haspopup' => 'true',
                'aria-expanded' => 'false',
                'escape' => false
            ];
            $linkOptions = $this->addClass($linkOptions, 'nav-link dropdown-toggle');
            $link  = $this->Html->link($name, $url ? $url : '#', $linkOptions);
            $options     = $this->addClass($options, 'nav-item dropdown');
            $listOptions = $this->addClass($listOptions, 'dropdown-menu');
            $res = $this->Html->tag('li', null, $options)
                 .$link.$this->Html->tag('div', null, $listOptions);
        }
        $this->_level += 1;
        return $res;
    }

    /**
     * End a menu.
     *
     * @return string HTML elements to close a menu.
     */
    public function endMenu() {
        $out = '';
        if ($this->_level == 1) {
            $out = '</ul>';
        }
        if ($this->_level == 2) {
            $out = '</div></li>';
        }
        $this->_level -= 1;
        return $out;
    }

    /**
     * Close a navbar.
     *
     * @return string HTML elements to close the navbar.
     */
    public function end() {
        $out = '';
        if ($this->_responsive) {
            $out .= '</div>';
        }
        $out .= '</nav>';
        if ($this->_container) {
            $out .= '</div>';
        }
        return $out;
    }

}

?>
