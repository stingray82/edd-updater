edd-updater
===========

An example EDD plugin Updater / Loader for testing

 

This is WIP and not a tested product it hasn’t ever been tested with EDD yet

 

Bootstrap Examples

 

Settings Page (Default)

`new RUP_EDD_Loader([`

`'plugin_file'    => __FILE__,`

`'plugin_version' => '1.0.0',`

`'store_url'      => 'https://yourstore.com',`

`'item_id'        => 1234,`

`'item_name'      => 'My Plugin',`

`'author'         => 'Your Company',`

`'option_key'     => 'my_plugin_license_key',`

`'menu_slug'      => 'my-plugin-license',`

`'menu_type'      => 'settings',`

`]);`

 

 

Top Level Admin

 

`new RUP_EDD_Loader([`

`'plugin_file'    => __FILE__,`

`'plugin_version' => '1.0.0',`

`'store_url'      => 'https://yourstore.com',`

`'item_id'        => 1234,`

`'item_name'      => 'My Plugin',`

`'author'         => 'Your Company',`

`'option_key'     => 'my_plugin_license_key',`

`'menu_slug'      => 'my-plugin-license',`

`'menu_type'      => 'top',`

`]);`

Submenu under existing plugin Menu

 

`new RUP_EDD_Loader([`

`'plugin_file'    => __FILE__,`

`'plugin_version' => '1.0.0',`

`'store_url'      => 'https://yourstore.com',`

`'item_id'        => 1234,`

`'item_name'      => 'My Plugin',`

`'author'         => 'Your Company',`

`'option_key'     => 'my_plugin_license_key',`

`'menu_slug'      => 'my-plugin-license',`

`'menu_type'      => 'submenu',`

`'parent_slug'    => 'my-main-plugin-menu', // The slug of the parent plugin's
menu`

`]);`

 

 

`/**`

`* Bootstrap the RUP EDD Loader for this plugin.`

`*/`

`function my_plugin_load_edd_license_manager() {`

`if ( ! class_exists( 'RUP_EDD_Loader' ) ) {`

`require_once plugin_dir_path( __FILE__ ) . 'inc/class-rup-edd-loader.php';`

`}`

 

`new RUP_EDD_Loader([`

`'plugin_file'    => __FILE__,`

`'plugin_version' => '1.0.0',`

`'store_url'      => 'https://example.com',`

`'item_id'        => 123,`

`'item_name'      => 'RUP EDD Demo Plugin',`

`'author'         => 'Your Company',`

`'menu_type'      => 'top',`

`'menu_slug'      => 'rup-demo-license',`

`'menu_title'     => 'Awesome License',`

`'page_title'     => 'My Plugin Settings',`

`]);`

`}`

`add_action( 'plugins_loaded', 'my_plugin_load_edd_license_manager' );`
