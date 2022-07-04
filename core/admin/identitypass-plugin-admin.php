<?php
    class IdentitypassAdmin {

        private $plugin_name;
        private $version;

        public function __construct( $plugin_name, $version )
        {

            $this->plugin_name = $plugin_name;
            $this->version = $version;

            add_action( 'admin_menu', 'idplugin_add_settings_page' );
            
            add_action( 'admin_init', 'idplugin_register_setting_page' );

            add_action( 'init',  'register_idx_idcheckout' );

            function register_idx_idcheckout()
            {
                
                $labels = array(
                    'name' => _x('Payment Forms', 'identity_configuration_form'),
                    'singular_name' => _x('Identitypass Form', 'identity_configuration_form'),
                    'add_new' => _x('Add New', 'identity_configuration_form'),
                    'add_new_item' => _x('Add Identitypass Form', 'identity_configuration_form'),
                    'edit_item' => _x('Edit Identitypass Form', 'identity_configuration_form'),
                    'new_item' => _x('Identitypass Form', 'identity_configuration_form'),
                    'view_item' => _x('View Identitypass Form', 'identity_configuration_form'),
                    'all_items' => _x('All Forms', 'identity_configuration_form'),
                    'search_items' => _x('Search Identitypass Forms', 'identity_configuration_form'),
                    'not_found' => _x('No Identitypass Forms found', 'identity_configuration_form'),
                    'not_found_in_trash' => _x('No Identitypass Forms found in Trash', 'identity_configuration_form'),
                    'parent_item_colon' => _x('Parent Identitypass Form:', 'identity_configuration_form'),
                    'menu_name' => _x('Identitypass Forms', 'identity_configuration_form'),
                );

                $args = array(
                    'labels' => $labels,
                    'hierarchical' => true,
                    'description' => 'Identitypass Forms filterable by genre',
                    'supports' => array('title', 'editor'),
                    'public' => true,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'menu_position' => 5,
                    'menu_icon' => plugins_url('../../resources/sc_images/idpass-logo.png', __FILE__),
                    'show_in_nav_menus' => true,
                    'publicly_queryable' => true,
                    'exclude_from_search' => false,
                    'has_archive' => false,
                    'query_var' => true,
                    'can_export' => true,
                    'rewrite' => false,
                    'comments' => false,
                    'capability_type' => 'post'
                );
    
                register_post_type( 'identity_configuration_form', $args );
            }

            function idx_identity_add_view_payments($actions, $post)
            {
                if ( get_post_type() === 'identity_configuration_form' ) {
                    unset($actions['view']);
                    unset($actions['quick edit']);
                    $url = add_query_arg(
                        array(
                            'post_id' => $post->ID,
                            'action' => 'submissions',
                        )
                    );
                    $actions['export'] = '<a href="' . admin_url('admin.php?page=submissions&form=' . $post->ID) . '" >View Payments</a>';
                }
                return $actions;
            }

            // add_filter('page_row_actions', 'idx_identity_add_view_payments', 10, 2);
            // plugin_dir_path( __FILE__ ) . '../../resources/sc_images/idpass-logo.png'

            function idplugin_add_settings_page()
            {
                add_menu_page(
                    __( 'Identitypass', 'identitypass_checkout' ),
                    __( 'Identitypass', 'identitypass_checkout' ),
                    'manage_options',
                    'identitypass_checkout', // what is displayed as the name on the url
                    'wpplugin_settings_page_markup',
                    plugins_url('../../resources/sc_images/idpass-logo.png', __FILE__),
                    // 'dashicons-wordpress-alt',
                    // plugins_url('../sc_images/idpass-logo.png', __FILE__),
                    5
                );
                
                add_submenu_page(
                    'identitypass_checkout',
                    __( 'Configuration', 'identitypass_checkout' ),
                    __( 'Configuration', 'identitypass_checkout' ),
                    'manage_options',
                    'edit.php?post_type=identity_kyc_config',
                    'show_admin_settings_screen',
                );
                // add_submenu_page('edit.php?post_type=identity_configuration_form', 'Configuration', 'Configuration', 'edit_posts', basename(__FILE__), 'show_admin_settings_screen');
            }
            // background-image: url('. get_field ("option", "logo_image") . ');

            // function admin_style() {
            //     echo '<style>
            //        #toplevel_page_logo_based_menu {
            //             background-image: url('. plugin_dir_path( __FILE__ ) . '../../resources/sc_images/idpass-logo.png' . ');
            //             // background-image: url('. get_field ("option", "logo_image") . ');
            //         }
            //                 #toplevel_page_logo_based_menu > a, #toplevel_page_logo_based_menu > a > div.wp-menu-image {
            //             display: none;
            //         }
            //      </style>';
            // }
            // add_action('admin_enqueue_scripts', 'admin_style');

            function wpplugin_settings_page_markup()
            {
                if(!current_user_can('manage_options')) {
                    return;
                }

                echo '<p><h1>Dashboard display overview coming soon!</h1></p>';
            }

            function kyc_mode_check($name, $txncharge)
            {
                if ($name == $txncharge) {
                    $result = "selected";
                } else {
                    $result = "";
                }
                return $result;
            }

            function show_admin_settings_screen()
            {
    
    ?>
                <div class="wrap">
                    <h1>Identitypass KYC Configuration</h1>
                    <h2>API Keys Settings</h2>
                    <span>Get your API Keys <a href="settings/developer" target="_blank">here</a> </span>
                    <form method="post" action="options.php">
                        <?php settings_fields('idplugin-settings-pallet');
                        do_settings_sections('idplugin-settings-pallet'); ?>
                        <table class="form-table setting_page">
                            <tr valign="top">
                                <th scope="row">KYC Mode</th>
    
                                <td>
                                    <select class="form-control" name="kyc_mode" id="parent_id">
                                        <option value="test" <?php echo kyc_mode_check('test', esc_attr(get_option('kyc_mode'))) ?>>Test Mode</option>
                                        <option value="live" <?php echo kyc_mode_check('live', esc_attr(get_option('kyc_mode'))) ?>>Live Mode</option>
                                    </select>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Test Secret Key</th>
                                <td>
                                    <input type="text" name="kyc_tsk" value="<?php echo esc_attr(get_option('kyc_tsk')); ?>" />
                                </td>
                            </tr>
    
                            <tr valign="top">
                                <th scope="row">Test Public Key</th>
                                <td><input type="text" name="kyc_tpk" value="<?php echo esc_attr(get_option('kyc_tpk')); ?>" /></td>
                            </tr>
    
                            <tr valign="top">
                                <th scope="row">Live Secret Key</th>
                                <td><input type="text" name="kyc_lsk" value="<?php echo esc_attr(get_option('kyc_lsk')); ?>" /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Live Public Key</th>
                                <td><input type="text" name="kyc_lpk" value="<?php echo esc_attr(get_option('kyc_lpk')); ?>" /></td>
                            </tr>
    
                        </table>
    
                        <hr>
    
                        <?php submit_button(); ?>
                    </form>
                </div>
            <?php
            }
            
            function idplugin_register_setting_page()
            {
                register_setting('idplugin-settings-pallet', 'kyc_mode');
                register_setting('idplugin-settings-pallet', 'kyc_tsk');
                register_setting('idplugin-settings-pallet', 'kyc_tpk');
                register_setting('idplugin-settings-pallet', 'kyc_lsk');
                register_setting('idplugin-settings-pallet', 'kyc_lpk');
            }
        }

        public function initplugin_script()
        {
            wp_register_script( 'Idx_plugin', 'https://js.myidentitypay.com/v1/inline/kyc.js', false, '1');
            wp_enqueue_script( 'Idx_plugin' );
        }

        public function add_custom_action_links( $links )
        {
            $settings_link = array(
                '<a href="' . admin_url('admin.php?page=edit.php?post_type=identity_kyc_config') . '">' . __('Configuration', $this->plugin_name) . '</a>',
            );
            return array_merge($settings_link, $links);
        }
    }

    if ( !class_exists('WP_List_Table') ) {
        include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }


    class All_Verifications_Table extends WP_List_Table
    {
        public function prepare_items()
        {
            $post_id = $_GET['form'];
            $currency = get_post_meta($post_id, '_currency', true);
    
            global $wpdb;
    
            $table = $wpdb->prefix . 'id';
            $data = array();
            $alldbdata = $wpdb->get_results("SELECT * FROM $table WHERE ()");
    
            foreach ($alldbdata as $key => $dbdata) {
                $newkey = $key + 1;
                if ($dbdata->txn_code_2 != "") {
                    $txn_code = $dbdata->txn_code_2;
                } else {
                    $txn_code = $dbdata->txn_code;
                }
                $data[] = array(
                    'id'  => $newkey,
                    'email' => '<a href="mailto:' . $dbdata->email . '">' . $dbdata->email . '</a>',
                    'firstname' => $currency . '<b>' . number_format($dbdata->amount) . '</b>',
                    'lastname' => $txn_code,
                    'phone' => format_data($dbdata->metadata),
                    'date'  => $dbdata->created_at
                );
            }
    
            $columns = $this->get_columns();
            $hidden = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();
            usort($data, array(&$this, 'sort_data'));
            $perPage = 20;
            $currentPage = $this->get_pagenum();
            $totalItems = count($data);
            $this->set_pagination_args(
                array(
                    'total_items' => $totalItems,
                    'per_page'    => $perPage
                )
            );
            $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items = $data;
    
            $rows = count($alldbdata);
            return $rows;
        }
    
        public function get_columns()
        {
            $columns = array(
                'id'  => '#',
                'email' => 'Email',
                'firstname' => 'Firstname',
                'lastname' => 'Lastname',
                'metadata' => 'Data',
                'date'  => 'Date'
            );
            return $columns;
        }
        /**
         * Define which columns are hidden
         *
         * @return Array
         */
        public function get_hidden_columns()
        {
            return array();
        }
        public function get_sortable_columns()
        {
            return array('email' => array('email', false), 'date' => array('date', false), 'amount' => array('amount', false));
        }
        /**
         * Get the table data
         *
         * @return Array
         */
        private function table_data($data)
        {
            return $data;
        }
        /**
         * Define what data to show on each column of the table
         *
         * @param Array  $item        Data
         * @param String $column_name - Current column name
         *
         * @return Mixed
         */
        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'id':
                case 'email':
                case 'firstname':
                case 'lastname':
                case 'metadata':
                case 'date':
                    return $item[$column_name];
                default:
                    return print_r($item, true);
            }
        }
    
        /**
         * Allows you to sort the data by the variables set in the $_GET
         *
         * @return Mixed
         */
        private function sort_data($a, $b)
        {
            $orderby = 'date';
            $order = 'desc';
            if (!empty($_GET['orderby'])) {
                $orderby = $_GET['orderby'];
            }
            if (!empty($_GET['order'])) {
                $order = $_GET['order'];
            }
            $result = strcmp($a[$orderby], $b[$orderby]);
            if ($order === 'asc') {
                return $result;
            }
            return -$result;
        }
    }