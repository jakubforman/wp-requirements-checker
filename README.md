# WP Requirements Checker

Lightweight validator library class for check PHP version, PHP extensions, plugins and theirs versions in Wordpress
Plugins.

## Usage

Pass the requirements to a new instance of this class like so:

```php
use jayjay666\WPRequirementsChecker\Validator;

// ... code before plugin/theme init


// ... start init code with
// Set PHP version, plugin root file path and plugin text domain
$validator = new Validator('7.1', 'my-awesome-plugin/my-awesome-plugin.php', 'my-awesome-plugin');

// Set plugins requirements
$validator->add_required_plugin('woocommerce/woocommerce','1.2.1');
$validator->add_required_plugin('elementor/elementor.php','3.0');

// Set PHP extensions requirements
$validator->add_required_extensions('curl');

if (!$validator->check()) {
    // ... min. requirements not valid. Automatic print error & disable plugin in check() method ad break code
    return;
}

// ... if requirements is valid, run your plugin code
// ... your plugin init code for start plugin
```

## Implementation & Instalation

There are two ways you can include WP Requirements in your project.

**Copy this class (not recommended)**

You can copy the class found in `/src/Validator.php` in this project.

> Important! If you choose to do so, please rename this class with the prefix used by your project 
> (for example: from Validator to My_Plugin_Validator). 
> In this way there is less risk of a naming collision between projects.
> Also change namespace!

In case you want to include the file manually, please wrap the include or require call in a class_exists conditional, like so:

```php
// Namespace & prefix must be yours!!!
if (!class_exists( 'jayjay666\WPRequirementsChecker\Validator') ) {
    // do the file include or require here
} 
```

**Use composer (recommended way)**

Include this library with:

`$ composer require jayjay666/wp-requirements-checker`

### Usage example

```php
use Elementor\Controls_Manager;use Elementor\Element_Section;
use jayjay666\WPRequirementsChecker\Validator;

class MyAwesomePlugin{
    const DOMAIN = 'my-awesome-plugin';
    
    private static $_instance = null;

    public static function instance()
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
    }
    
    public function init()
    {
        // Check requirements
        $validator = new Validator('7.1', 'my-awesome-plugin/my-awesome-plugin.php', MyAwesomePlugin::DOMAIN);
        // OR $validator = new Validator('7.1', 'my-awesome-plugin/my-awesome-plugin.php', self::DOMAIN);

        $validator->add_required_plugin('elementor/elementor.php','3.0');
        if (!$validator->check()) {
            return;
        }

        // add controlls to elementor
        add_action('elementor/element/section/section_layout/before_section_end', [__CLASS__, 'add_section_controls']);
    }
  
    public static function add_section_controls(Element_Section $element)
    {
        //
        // Přidám responzivní pro zarovnání slopců
        $element->add_responsive_control(
            'scb_section_horizontal_align',
            [
                'label' => __('Horizontal align', MyAwesomePlugin::DOMAIN),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Default', 'elementor'),
                    'flex-start' => __('Start', 'elementor'),
                    'flex-end' => __('End', 'elementor'),
                    'center' => __('Center', 'elementor'),
                    'space-between' => __('Space Between', 'elementor'),
                    'space-around' => __('Space Around', 'elementor'),
                    'space-evenly' => __('Space Evenly', 'elementor'),
                ],
                'selectors' => [ // Zde se předají data do CSS
                    '{{WRAPPER}} .elementor-row' => 'justify-content: {{VALUE}};',
                ],
            ]
        );
    }
}
```

### Tested on

**Minimal requirements**

|  | Version |
|---|---|
| PHP | 7.1 |
| WordPress | 5.5 |