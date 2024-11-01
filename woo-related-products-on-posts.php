<?php
/*
Plugin Name: Woo Related Products on Posts
Plugin URI: https://said.solutions/
Description: Allows Display of Selected WooCommerce Products on Posts.
Author: Matt Shirk
Version: 3.2
Author URI: https://said.solutions/
*/

// Add Admin Page to Wordpress Admin Dashboard Menu
function wrpp_adminpage_menu() {
	add_options_page( 'Display Related Products on Posts', 'Related Products on Posts', 'manage_options', 'select-parent-category', 'wrpp_parent_category_selection' );
}
add_action( 'admin_menu', 'wrpp_adminpage_menu' );

// Sanitize all illegal characters from a string
function wrpp_sanitize_string($WRPP_thisString){
	$WRPP_thisString = filter_var($WRPP_thisString, FILTER_SANITIZE_STRING);
	return $WRPP_thisString;
}

// Admin Settings Screen 
function wrpp_parent_category_selection() {

//Register and enqueue stylesheet for plugin admin page
	wp_register_style( 'wrpp_adminstyles', plugin_dir_url( __FILE__ ) . 'wrpp_adminstyles.css' );
	wp_enqueue_style('wrpp_adminstyles');

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

//Begin Admin Page HTML
	echo '<div class="wrap wrppContainer">';

//"Leave a Review" box HTML
	echo '<div id="upgradeBox" style="">';
	echo '<h2 style="border-bottom:0px;">Do you find this plugin useful? <a target="_blank" href="https://wordpress.org/plugins/woo-simply-add-related-products-to-blog-posts/">Leave a Review Now!</a></h2>';
	echo '</div>';

//Admin Instructions HTML - User must enter Blog Parent Category
	echo '<h1 style="border-top: 2px solid black; margin-top: 40px; padding-top: 40px; padding-bottom: 20px; font-size:24px; font-weight:bold;">Woo Simply Add Related Products to Blog Posts</h1><div><p>Adding related products to blog posts is now simple!</p><ul style="list-style:decimal;padding-left:20px;"><li>First Select the top level Parent Category that all your blog posts are in.</li><li>You can now add related products to posts on the individual "edit post" pages.</li></ul></div><br><br>';
	echo '<div style=\"border:2px solid gray; background-color:white; width:50%; height:400px; overflow:hidden; overflow-y:scroll;\">';
	
	echo '</div>';

	$wrpp_justSubmitted = isset($_POST['wrppnewcategoryselect']);

	if ($wrpp_justSubmitted) {

		$wrpp_justSubmittedTerm = $_POST['wrppnewcategoryselect'];

		$wrpp_justSubmittedCategory = get_category_by_slug($wrpp_justSubmittedTerm);

//echo var_dump($wrpp_justSubmittedCategory);

		$wrpp_thisOneIDz = $wrpp_justSubmittedCategory->cat_ID;
		$wrpp_thisOneNamez = $wrpp_justSubmittedCategory->name;
		$wrpp_thisOneSlugz = $wrpp_justSubmittedCategory->slug;
		$wrpp_thisOneParentz = $wrpp_justSubmittedCategory->parent;


		$wrpp_justSubmittedTerma = wrpp_sanitize_string($_POST['wrppnewcategoryselect']);

//If user has entered Blog Category, submit to database
		if (isset($wrpp_justSubmittedTerma)){
			global $wpdb;
			$wrpp_DBnamee = 'wooRelatedProducts';
			$wrpp_DBname = $wpdb->prefix . $wrpp_DBnamee;
			$wpdb->update( 
				$wrpp_DBname, 
				array( 
					'blogCatName' => $wrpp_thisOneNamez,
					'blogCatSlug' => $wrpp_thisOneSlugz,
					'blogCatParent' => $wrpp_thisOneParentz,
					'blogCatId' => $wrpp_thisOneIDz
				), 
				array( 'id' => 1 ), 
				array( 
        '%s',   // value1
        '%s',   // value2
        '%s',   // value3
        '%s'
    ), 
				array( '%d' ) 
			);
		}
		echo '<style>div.error.notice{
			display:none !important;
		}</style>';


		echo '<h1><span class="dashicons dashicons-yes">&nbsp;</span>Plugin Correctly Configured</h1>';
		echo '<h2 class="active">CURRENT BLOG PARENT CATEGORY: ' . $wrpp_thisOneNamez . '</h2>';

		echo '<div style=\"border:2px solid gray; background-color:white; width:50%; height:400px; overflow:hidden; overflow-y:scroll;\"><form style="height:100px;" action="' . admin_url('options-general.php?page=select-parent-category') . '" method="post">';
		echo wrpp_getBlogCats() . '<br />
		<button class="submitButtton" type="submit" name="submit">Update Blog Parent Category</button>
		</form>';

	} else {

		$WRPP_CATNAME = wrpp_getTheCategoryName();

		$wrpp_isThisAlreadyActive = 0;

		if ($WRPP_CATNAME) {
			$wrpp_isThisAlreadyActive = 1;
			echo '<h1><span class="dashicons dashicons-yes">&nbsp;</span>Plugin Correctly Configured</h1>';
			echo '<h2 class="active">CURRENT BLOG PARENT CATEGORY: ' . $WRPP_CATNAME . '</h2>';
			echo '<div style=\"border:2px solid gray; background-color:white; width:50%; height:400px; overflow:hidden; overflow-y:scroll;\"><form style="height:100px;" action="' . admin_url('options-general.php?page=select-parent-category') . '" method="post">';
			echo wrpp_getBlogCats() . '<br />
			<button class="submitButtton" type="submit" name="submit">Update Blog Parent Category</button>
			</form>';

			echo '</div><br>';

		} else {
			echo '<h1><span class="dashicons dashicons-no">&nbsp;</span>Plugin Not Configured</h1>';
			echo '<h2 class="nonactive">CURRENT BLOG PARENT CATEGORY: NOT SET</h2>';
			echo '<div style=\"border:2px solid gray; background-color:white; width:50%; height:400px; overflow:hidden; overflow-y:scroll;\"><form style="height:100px;" action="' . admin_url('options-general.php?page=select-parent-category') . '" method="post">';
			echo wrpp_getBlogCats() . '<br />
			<button class="submitButtton" type="submit" name="submit">Submit</button>
			</form>';
			echo '</div><br>';
		}
	}
}

// Get all blog post categories so the user can select the parent category to enable product selection
function wrpp_getBlogCats(){

//is there already a selected category?
	$wrpp_isThisCategory = wrpp_getTheCategoryName();

	if ($wrpp_isThisCategory) {
		$theCurrentCategory = $wrpp_isThisCategory;
	} else {
		$theCurrentCategory = '';
	}
	
	$wrpp_getTheBlogCategories = get_categories();
	//echo var_dump($wrpp_getTheBlogCategories);

	$wrpp_allTheSelectionss = '';
	foreach ($wrpp_getTheBlogCategories as $wrpp_singleCat){
		$wrpp_thisOneID = $wrpp_singleCat->cat_ID;
		$wrpp_thisOneName = $wrpp_singleCat->name;
		$wrpp_thisOneSlug = $wrpp_singleCat->slug;
		$wrpp_thisOneParent = $wrpp_singleCat->parent;

		$wrpp_selectedInsertDefault = 'selected=""';

		if ($wrpp_thisOneParent == 0) {

			if ($wrpp_thisOneName == $theCurrentCategory) {
				$wrpp_selectedInsert = 'selected=""';
				$wrpp_selectedInsertDefault = '';
			} else {
				$wrpp_selectedInsert = '';
			}

			$wrpp_allTheSelectionss = '<option ' . $wrpp_selectedInsert . ' value="' . $wrpp_thisOneSlug . '">' . $wrpp_thisOneName . '</option>' . $wrpp_allTheSelectionss;
		} else {

			if ($wrpp_thisOneName == $theCurrentCategory) {
				$wrpp_selectedInsert = 'selected=""';
				$wrpp_selectedInsertDefault = '';
			} else {
				$wrpp_selectedInsert = '';
			}

			$wrpp_allTheSelectionss = $wrpp_allTheSelectionss . '<option ' . $wrpp_selectedInsert . ' value="' . $wrpp_thisOneSlug . '">' . $wrpp_thisOneName . '</option>';
		}

	}

//Display all the Blog Categories in a "Select" dropdown
	return '<select name="wrppnewcategoryselect" id="wrppnewcategoryselect"><option ' . $wrpp_selectedInsertDefault . ' value="" disabled="" >Select your blog\'s parent category</option>' . $wrpp_allTheSelectionss . '</select>';
}

// Remove bad stuff from any string entered
function wrpp_xss_strip($wrpp_input) {
	$wrpp_input = strip_tags($wrpp_input);
	$wrpp_input = htmlspecialchars($wrpp_input);
	$wrpp_input = preg_replace("/['\"&()]<>/","",$wrpp_input);
	return $wrpp_input; 
}

// Get all products from WooCommerce DB Tables
global $wpdb;
$wrpp_tnme = $wpdb->prefix . 'posts';
$wrpp_relateddProds = $wpdb->get_results( "SELECT post_title, ID 
	FROM $wrpp_tnme 
	WHERE post_type = 'product' 
	AND post_title != 'Auto Draft' 
	GROUP BY ID ASC", OBJECT );


// Adds the Related Product Selection meta box in the Blog Post Editor page
function wrpp_custom_metaaa() {
	add_meta_box( 'wrpp_metaaa', __( 'Add WooCommerce Related Products to Bottom of This Post  (Note: 4 Works Best)', 'prfx-textdomain' ), 'wrpp_metaaa_callback', 'post' );
}
add_action( 'add_meta_boxes', 'wrpp_custom_metaaa',9999 );


// Outputs the Related Product Selection meta box in the Blog Post Editor page
function wrpp_metaaa_callback( $post ) {

	wp_register_style( 'wrpp_styles', plugin_dir_url( __FILE__ ) . 'wrppstyles.css' );
	wp_enqueue_style('wrpp_styles');

	wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
	$wrpp_stored_meta = get_post_meta( $post->ID ); 

// Output HTML for selecting Related Products on the Blog Post Editor page ?>
	<p>
		<span class="prfx-row-title"><?php _e( 'Select Related Products that will appear at the bottom of this post', 'prfx-textdomain' )?></span>
		<div class="prfx-row-content">
			<?php $wrpp_productCount = 0;
			global $wrpp_relateddProds;
			foreach ($wrpp_relateddProds as $wrpp_relateddProd) {
				$wrpp_prodpostid = $wrpp_relateddProd->ID;
				$wrpp_prodposttitle = $wrpp_relateddProd->post_title;
				$wrpp_prodposttitle = wrpp_xss_strip($wrpp_prodposttitle);

				$wrpp_productCount++;
				$wrpp_itemnm = 'wrpp-meta-checkbox' . $wrpp_productCount; ?>
				<div class="productSelection" style="display:inline-block;margin:0px auto;height:20px;vertical-align:top;min-width:10%">	
					<label class="relprodsadmin" for="<?php echo $wrpp_itemnm;?>">
						<input onclick="wrpp_deeezzCheckBxxs()" class="deeezCheckboxs" type="checkbox" name="<?php echo $wrpp_itemnm;?>" id="<?php echo $wrpp_itemnm;?>" value="yes" <?php if ( isset ( $wrpp_stored_meta[$wrpp_itemnm] ) ) checked( $wrpp_stored_meta[$wrpp_itemnm][0], $wrpp_prodpostid ); ?> />
						<?php _e( $wrpp_prodposttitle, 'prfx-textdomain' )?>
					</label>
				</div>
			<?php } ?>
			<script>
				function wrpp_deeezzCheckBxxs(){
					var wrpp_theseCheckBoxxs = document.getElementsByClassName('deeezCheckboxs');
					for(i=0;i<wrpp_theseCheckBoxxs.length;i++){
						var wrpp_checkBxxComputedStyle = window.getComputedStyle(wrpp_theseCheckBoxxs[i], ":before/:after"); 
						if(wrpp_checkBxxComputedStyle = true){

						} else {

						}
					}
				}
			</script>
		</div>
	</p>
<?php }
// This Function saves the custom meta input on blog post editor page
function wrpp_metadata_save( $post_id ) {

	// Checks save status
	$wrpp_is_autosave = wp_is_post_autosave( $post_id );
	$wrpp_is_revision = wp_is_post_revision( $post_id );
	$wrpp_is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

// Exits script depending on save status
	if ( $wrpp_is_autosave || $wrpp_is_revision || !$wrpp_is_valid_nonce ) {
		return;
	}

// Checks for input and sanitizes/saves if needed
	if( isset( $_POST[ 'meta-text' ] ) ) {
		update_post_meta( $post_id, 'meta-text', sanitize_text_field( $_POST[ 'meta-text' ] ) );
	}

// gets selected products
	$wrpp_productCountz = 0;
	global $wrpp_relateddProds;
	foreach ($wrpp_relateddProds as $wrpp_relateddProd) {
		$wrpp_rr = $wrpp_relateddProd->ID;
		$wrpp_productCountz++;
		$wrpp_itemnmz = 'wrpp-meta-checkbox' . $wrpp_productCountz;

// Checks for input and saves
		if( isset( $_POST[ $wrpp_itemnmz ] ) ) {
			update_post_meta( $post_id, $wrpp_itemnmz, $wrpp_rr );
		} else {
			update_post_meta( $post_id, $wrpp_itemnmz, '' );
		}
	}
}
add_action( 'save_post', 'wrpp_metadata_save' );


// This function defines the woocommerce_after_main_content callback - this places the related products after the post content
function wrpp_displayProducts($wrpp_content) {

	$userSelectedCategory = wrpp_getTheCategoryName();

// Checks to make sure this is a regular post and nothing else
	if ( is_single() && in_category($userSelectedCategory))  {
		$wrpp_productCountzz = 0;
		global $wrpp_relateddProds;
		$wrpp_countter = 0;
		$wrpp_contenta = '';
		foreach ($wrpp_relateddProds as $wrpp_relateddProd) {
			$wrpp_productCountzz++;
			$wrpp_id = $wrpp_relateddProd->ID;
			$_product = wc_get_product( $wrpp_id );
			$wrpp_price = $_product->get_price();

			$wrpp_itemnmzz = 'wrpp-meta-checkbox' . $wrpp_productCountzz;
			$wrpp_key_value = get_post_meta( get_the_ID(), $wrpp_itemnmzz, true );
			$wrpp_add_to_cart = do_shortcode('[add_to_cart_url id="' . $wrpp_id .'"]');

// Check if the custom field has a value.
			if ( !empty( $wrpp_key_value ) ) {
				$wrpp_tit = get_the_title($wrpp_id);
						//$wrpp_tit = wrpp_xss_strip($wrpp_tit);
				$wrpp_tit_length = strlen($wrpp_tit);

				$wrpp_img = get_the_post_thumbnail( $wrpp_id, 'post-thumbnail',array('title' => '' . $wrpp_tit . '','alt' => '' . $wrpp_tit . '') );
				$wrpp_link = get_permalink($wrpp_id); 
				$wrpp_countter++;
				$wrpp_contenta = $wrpp_contenta . '<div class="relprods" style="display:inline-block;">
				<div class="prodImg"><a href="' . $wrpp_link . '">' . $wrpp_img . '</a></div><div class="prodTitt"><h4 class="prodTitth4"><a href="' . $wrpp_link . '">' . $wrpp_tit . '</a></h4><p class="prodPricce">$' . $wrpp_price . '</p></div><a class="wrppAddToCart" style="" href="' . $wrpp_add_to_cart . '">Add To Cart</a></div>';
			}
		}

// First check if there are Products and then check if there are Related Products for this blog post
		if ($wrpp_productCountzz > 0) {
			if ($wrpp_countter > 0) {
				$wrpp_styles = '<style>
				a.wrppAddToCart {
					color:white;
					background-color:black;
					font-weight:500 !important ;
					max-width:200px;
					margin:0px auto;
					padding:5px;
				}
				a.wrppAddToCart:hover {
					color: white !important;
					font-weight: bolder !important;
				}
				h4.prodTitth4{
					padding:0px;
				}
				div.prodImg{
					min-height:100px;
				}
				div.relprods span{

				}
				div.relprods p{
					border:0px !important;
				}
				div.relprods {
					width: 20%;
					margin: 0px 2.5%;
					margin-bottom:20px;
				}
				div.relprods a {
					text-align:center;
					display:block !important;
				}
				h2.relprodtit{
					text-align: center;
					border-bottom: 1px solid;
					margin-bottom: 20px;
					padding-bottom: 10px;
				}
				div.relprodscontainer {
					text-align: center;
					border-bottom: 1px solid;
					padding-bottom: 20px;
				}
				@media screen and (max-width:1024px){
					div.relprods {
						width: 44%;
						margin: 0px 2.5%;
					}
				}
				@media screen and (max-width:1024px){
					div.relprods {
						width: 44%;
						margin: 0px 2.5%;
					}
				}
				</style>'; } else {
					$wrpp_styles = '<style></style>';
				}

				if ($wrpp_countter > 0) {
					$wrpp_content = $wrpp_content . '<div class="relprodscontainer"><h2 class="relprodtit">Related Products:</h2>' . $wrpp_contenta . '</div>' . $wrpp_styles;}
					return $wrpp_content;
				}

			} elseif(is_archive()){
				//echo strip_tags(substr($wrpp_content, 0,500)) . '.....<br>';
			}

			else  {
				return $wrpp_content;

			}
		}

// Add the action that displays the related products after the content
		add_filter( 'the_content', 'wrpp_displayProducts', 999999, 2 );


//This function adds the meta box stylesheet when appropriate
		function wrpp_admin_stylessss(){
			global $wrpp_typenow;
			if( $wrpp_typenow == 'post' ) {
				wp_enqueue_style( 'wrpp_metaaa_box_styles', plugin_dir_url( __FILE__ ) . 'meta-box-styles.css' );
			}
		}
		add_action( 'admin_print_styles', 'wrpp_admin_stylessss' );


// This function drops the db table upon uninstall */
		function wrpp_pluginUninstall(){
			global $wpdb;
			$wrpp_tablee = 'wooRelatedProducts';
			$wrpp_table = $wpdb->prefix . $wrpp_tablee;
			$wrpp_dropIfExists = "DROP TABLE IF EXISTS $wrpp_table";
			$wpdb->query($wrpp_dropIfExists);
		}
		register_uninstall_hook( __FILE__, 'wrpp_pluginUninstall' );



//This function creates the notice to remind users to set blog parent category
		function wrpp_cat_config_notice() {
			$wrpp_checking_for_cat = wrpp_getTheCategoryName();
			if ($wrpp_checking_for_cat) {
		// category is set so do nothing

			} else {

					//check to see if fresh installation

// category is not set so display error box
				$wrpp_error_boxx = _e( '<div class="error notice"><p><strong>Woo Related Products on Blog Posts</strong></p><p>Related Products Plugin Not Configured</p><p>Please select a parent blog category for your related products <a href="' . admin_url('options-general.php?page=select-parent-category') . '">here</a></p></div>');
				return $wrpp_error_boxx;
			}
		}
		add_action( 'admin_notices', 'wrpp_cat_config_notice' );



// function to create the DB / Options / Defaults during initial installation
		function wrpp_pluginInstall() {
			global $wpdb;
			$wrpp_DBnamee = 'wooRelatedProducts';
			$wrpp_DBname = $wpdb->prefix . $wrpp_DBnamee;

				// create the new database table
			if($wpdb->get_var("show tables like '$wrpp_DBname'") != $wrpp_DBname){
				$WRPPsql = 'CREATE TABLE ' . $wrpp_DBname . '(
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`blogCatName` mediumtext NOT NULL,
				`blogCatSlug` mediumtext NOT NULL,
				`blogCatParent` mediumtext NOT NULL,
				`blogCatId` mediumtext NOT NULL,
				UNIQUE KEY id (id)
			);';

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($WRPPsql);

// Initial values entered into the db are blank
			$WRPPthisDefaultValue = '';
			$WRPPtheDefaultValue = $WRPPthisDefaultValue;
			$wpdb->insert( 
				$wrpp_DBname, 
				array( 
					'id' => 0,
					'blogCatName' => $WRPPtheDefaultValue,
					'blogCatSlug' => $WRPPtheDefaultValue,
					'blogCatParent' => $WRPPtheDefaultValue,
					'blogCatId' => $WRPPtheDefaultValue
				), 
				array( 
					'%d', 
					'%s', 
					'%s', 
					'%s', 
					'%s'
				) 
			);
		} else {
			$ggg = "DROP TABLE IF EXISTS $wrpp_DBname";
			$wpdb->query($ggg);

			$WRPPsql = 'CREATE TABLE ' . $wrpp_DBname . '(
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`blogCatName` mediumtext NOT NULL,
			`blogCatSlug` mediumtext NOT NULL,
			`blogCatParent` mediumtext NOT NULL,
			`blogCatId` mediumtext NOT NULL,
			UNIQUE KEY id (id)
		);';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($WRPPsql);

// Initial values entered into the DB are blank
		$WRPPthisDefaultValue = '';
		$WRPPtheDefaultValue = $WRPPthisDefaultValue;
		$wpdb->insert( 
			$wrpp_DBname, 
			array( 
				'id' => 0,
				'blogCatName' => $WRPPtheDefaultValue,
				'blogCatSlug' => $WRPPtheDefaultValue,
				'blogCatParent' => $WRPPtheDefaultValue,
				'blogCatId' => $WRPPtheDefaultValue
			), 
			array( 
				'%d', 
				'%s', 
				'%s', 
				'%s', 
				'%s'
			)  
		);
	}
}

// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'wrpp_pluginInstall');



// This function gets the blog category name currently saved in the wooRelatedProducts table
function wrpp_getTheCategoryName(){
	global $wpdb;
	$wrpp_DBnamee = 'wooRelatedProducts';
	$wrpp_DBname = $wpdb->prefix . $wrpp_DBnamee;

	if($wpdb->get_var("show tables like '$wrpp_DBname'") == $wrpp_DBname){ 
		$results = $wpdb->get_results( "SELECT *
			FROM $wrpp_DBname
			GROUP BY id ASC", OBJECT );
		if ($results) {
			$WRPP_CATNAME = $results[0]->blogCatName;
			return $WRPP_CATNAME;
		}
	} else {
		wrpp_pluginUninstall();
		wrpp_pluginInstall();
	}
}

// This function adds a "settings" link to the plugins page
function wrpp_plugin_add_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=select-parent-category">' . __( 'Settings' ) . '</a>';
	array_push( $links, $settings_link );
	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wrpp_plugin_add_settings_link' );
?>