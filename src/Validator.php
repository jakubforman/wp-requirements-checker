<?php


namespace jayjay666\WPRequirementsChecker;

/**
 * Class Validator
 *
 * Lightweight validator library class for check PHP version, PHP extensions, plugins and theirs versions in Wordpress Plugins
 *
 * @package jayjay666\WPPluginRequirements
 */
class Validator
{
    /**
     * Automatic plugin deactivation
     *
     * @var bool
     * @since 1.0
     */
    protected $deactivate_automatically = true;

    /**
     * Text domain of plugin
     *
     * @var string
     * @since 1.0
     */
    protected $text_domain;

    /**
     * Plugin base file path
     *
     * @var string
     * @since 1.0
     */
    protected $plugin; // = 'my-awesome-plugin/my-awesome-plugin.php';

    /**
     * Plugin data
     *
     * @var array
     * @since 1.0
     */
    private $plugin_data = [];

    /**
     * List of requirements errors
     *
     * @var array
     * @since 1.0
     */
    protected $requirements_errors = [];

    /**
     * List of requirements PHP extensions
     *
     * @var string[]
     * @since 1.0
     */
    protected $required_extensions = [
        // 'curl'
    ];

    /**
     * Minimal required version for plugin
     *
     * @var string
     * @since 1.0
     */
    protected $required_php_version = '7.0';

    /**
     * Group of required plugins
     *
     * @var array
     * @since 1.0
     */
    protected $required_plugins = [
        /*
        'elementor/elementor.php' => [
            'version' => '4.0.0',
            // 'extra_error_message' => 'Update it from official web.',
        ]
        */
    ];

    /**
     * Validator constructor.
     * @param string $required_php_version Minimum PHP version
     * @param string $plugin Plugin base file path. Example: 'my-awesome-plugin/my-awesome-plugin.php'
     * @param string $text_domain Text domain of plugin
     * @param bool $deactivate_automatically Can automatic deactivate plugin?
     */
    public function __construct(string $required_php_version, string $plugin, string $text_domain, $deactivate_automatically = true)
    {
        // Set PHP version
        $this->required_php_version = $required_php_version;

        // Set plugin base file path
        $this->plugin = $plugin;

        // Set Text domain of plugin
        $this->text_domain = $text_domain;

        // Set automatic deactivation
        $this->deactivate_automatically = $deactivate_automatically;


        // add functions only in admin for better performance & security issue
        if (is_admin()) {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
                $this->plugin_data = get_plugin_data(ABSPATH . 'wp-content/plugins/' . $this->plugin);
            }
        }
    }

    /**
     * Check if plugin can be activated
     *
     * @access public
     * @return bool
     * @since 1.0
     */
    public function check(): bool
    {
        // Clear errors
        $this->requirements_errors = [];

        // $this->check_required_extensions();
        if (is_admin()) {
            // Check PHP version
            $this->check_php_version();

            // Check PHP extensions
            $this->check_required_extensions();

            // run only if in admin
            $this->check_required_plugins();
        }

        // Check if have some error
        $result = empty($this->requirements_errors);

        // Show notice notification only if in admin page
        if (!$result && is_admin()) {
            // If requirements are missing, display the appropriate notices
            add_action('admin_notices', array($this, 'pluginRequirementsNotices'));
            // add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }

        return $result;
    }

    /**
     * Build notices error
     *
     * Show error of plugins & disable activation
     *
     * @access public
     * @since 1.0
     */
    public function pluginRequirementsNotices(): void
    {
        if ($this->deactivate_automatically) {
            $this->revert_activation();
        }

        $list = "";
        foreach ($this->requirements_errors as $requirements_error) {
            $list .= "<li>$requirements_error</li>";
        }
        printf('<div class="notice notice-error is-dismissible"><ul>%s</ul></div>', $list);
    }

    /**
     * Check PHP Extensions
     *
     * Checks that one or more PHP extensions are loaded & activated on server.
     *
     * @access protected
     * @since 1.0
     */
    protected function check_required_extensions(): void
    {

        foreach ($this->required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $this->requirements_errors[] = sprintf(__('Plugin<strong>%s</strong> requires <strong>%s</strong> PHP extension.', $this->text_domain),
                    isset($this->plugin_data['Name']) ? $this->plugin_data['Name'] : $this->text_domain,
                    $extension);
            }
        }
    }

    /**
     * Check all required plugins
     *
     * @access protected
     * @since 1.0
     */
    protected function check_required_plugins(): void
    {
        // projdu každý plugin, který potřebuji a zkontroluji ho
        foreach ($this->required_plugins as $requirementPluginName => $requirementPluginVersion) {

            // Check plugin and activation
            if (!is_plugin_active($requirementPluginName)) {
                $message = sprintf(__('Plugin <strong>%s</strong> requires plugin <strong>%s</strong>, must be installed and activated.', $this->text_domain),
                    isset($this->plugin_data['Name']) ? $this->plugin_data['Name'] : $this->text_domain, $requirementPluginName);
            } else {
                // Check plugin version
                $plugin_data = get_plugin_data(ABSPATH . 'wp-content/plugins/' . $requirementPluginName);

                if (!version_compare($plugin_data['Version'], $requirementPluginVersion['version'], '>=')) {
                    $message = sprintf(__('Plugin <strong>%s</strong> requires plugin <strong>%s</strong> in version <strong>%s</strong> or later. Installed version is <strong>%s</strong>.', $this->text_domain),
                        isset($this->plugin_data['Name']) ? $this->plugin_data['Name'] : $this->text_domain, $plugin_data['Name'], $requirementPluginVersion['version'], $plugin_data['Version']);
                }
            }

            // Add message to requirements errors
            if (!empty($message)) {
                if (isset($requirementPluginVersion['extra_error_message'])) {
                    $message .= ' ' . $requirementPluginVersion['extra_error_message'];
                }
                $this->requirements_errors[$requirementPluginName] = $message;
            }
        }
    }

    /**
     * Unset Activate
     *
     * Remove $_GET['activate]
     * Deactivate plugin
     *
     * @access private
     * @since 1.0
     */
    private function revert_activation(): void
    {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
        if (is_plugin_active(plugin_basename($this->plugin))) {
            deactivate_plugins(plugin_basename($this->plugin));
        }
    }

    /**
     * Check PHP version
     *
     * @access private
     * @since 1.0
     */
    private function check_php_version(): void
    {
        if (PHP_VERSION < $this->required_php_version) {
            $this->requirements_errors[] = sprintf(__('Plugin requires PHP %s or newer.', $this->text_domain),
                $this->required_php_version);
        }
    }

    /**
     * Add new required plugin
     *
     * @param $plugin string Plugin name path elementor-pro/elementor-pro.php
     * @param $version string Version of plugin
     * @param $message null|string Additional text
     * @return Validator
     * @since 1.0
     * @deprecated
     */
    public function addRequiredPlugin(string $plugin, string $version, $message = null): Validator
    {
        $pluginRequired = [
            'version' => $version,
            // 'extra_error_message' => 'Update it from official web.',
        ];
        if ($message) {
            $pluginRequired['extra_error_message'] = $message;
        }
        $this->required_plugins[$plugin] = $pluginRequired;
        return $this;
    }

    /**
     * Add new required plugin
     *
     * @param $plugin string Plugin name path elementor-pro/elementor-pro.php
     * @param $version string Version of plugin
     * @param $message null|string Additional text
     * @return Validator
     * @since 1.1
     */
    public function add_required_plugin(string $plugin, string $version, $message = null): Validator
    {
        $plugin_required = [
            'version' => $version,
            // 'extra_error_message' => 'Update it from official web.',
        ];
        if ($message) {
            $plugin_required['extra_error_message'] = $message;
        }
        $this->required_plugins[$plugin] = $plugin_required;
        return $this;
    }


    /**
     * Add new required PHP extension
     *
     * @param string $requiredExtension
     * @return Validator
     * @since 1.0
     * @deprecated
     */
    public function addRequiredExtensions(string $requiredExtension): Validator
    {
        $this->required_extensions[] = $requiredExtension;
        return $this;
    }

    /**
     * Add new required PHP extension
     *
     * @param string $required_extension
     * @return Validator
     * @since 1.1
     */
    public function add_required_extensions(string $required_extension): Validator
    {
        $this->required_extensions[] = $required_extension;
        return $this;
    }
}