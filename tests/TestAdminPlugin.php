<?php

namespace jayjay666\WPRequirementsChecker\Tests;

use jayjay666\WPRequirementsChecker\Validator;

class TestAdminPlugin extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        // Fake user
        $user_id = self::factory()->user->create(array('role' => 'administrator'));
        $user = wp_set_current_user($user_id);

        // set in admin page
        set_current_screen('edit-post');
    }

    /**
     * Necesssary clean-up work.
     */
    public function tearDown()
    {
        // Vyčistím testovací prostředí
        parent::tearDown();
    }

    /**
     * Check added PHP extensions
     */
    function testAddRequiredExtensionArray()
    {
        $validator = $this->validator->add_required_extensions('curl');
        $required_extensions = $this->get_value($validator, 'required_extensions');;
        $this->assertTrue(in_array('curl', $required_extensions) && count($required_extensions) == 1);
    }

    /**
     * Check added Wordpress plugins
     */
    function testAddRequiredPluginsArray()
    {
        $plugins_required = [
            'elementor/elementor.php' => '3.1.1',
            'woocommerce/woocommerce.php' => '1.2',
        ];

        foreach ($plugins_required as $plugin => $version) {
            $this->validator->add_required_plugin($plugin, $version);
        }

        $required_plugins = $this->get_value($this->validator, 'required_plugins');

        // map as version
        $compare1 = array_map(function ($array) {
            return $array['version'];
        }, $required_plugins);

        $this->assertTrue(count(array_diff($compare1, $plugins_required)) == 0, 'Required plugins not same!');
    }


    // TODO: dodělat test na kontrolu PHP verzí
    public function testPHPVersion()
    {
        self::assertTrue($this->validator->check());
    }

    // TODO: dodělat test validátor
}
