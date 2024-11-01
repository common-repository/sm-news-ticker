<?php 
/*
Plugin Name:SM News Ticker
Plugin URI:https://wordpress.org/plugins/sm-news-ticker
Author:Mahabubur Rahman
Author URI:http://www.mahabub.me
Version:1.0.0
Description: Wordpress plugin to <strong>show post as jQuery News Ticker.</strong>.
*/


/**
* 
*/
class SM_News_Ticker_Settings_Admin
{
	
	public function __construct()
	{
		add_action( 'wp_enqueue_scripts',array($this,'sm_news_ticker_scripts'));
		add_action('init',array($this,'sm_news_ticker_scripts'));
		add_action( 'admin_enqueue_scripts', array($this,'sm_news_ticker_admin_script'));
		add_action('admin_menu',array($this,'sm_news_ticker_add_menu_page'));
		add_action( 'admin_init', array( $this, 'sm_news_ticker_init' ) );
		add_shortcode( 'sm-news-ticker', array($this,'sm_news_ticker_shortcode'));
	}

	public function sm_news_ticker_scripts(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery.ticker',plugins_url('/assets/includes/jquery.ticker.js',__FILE__),array('jquery'),1.0,false);
		wp_enqueue_style('ticker-style',plugins_url('/assets/styles/ticker-style.css',__FILE__));		
	}

	public function sm_news_ticker_admin_script()
	{
		wp_enqueue_style('admin-style',plugins_url('/assets/styles/admin.css',__FILE__));
	}

	public function sm_news_ticker_add_menu_page(){
	    add_menu_page( 
	        __( 'SM News Ticker', 'textdomain' ),
	        'SM News Ticker',
	        'manage_options',
	        'sm-news-ticker',
	        array($this,'sm_news_ticker_menu_page'),
	        plugins_url( '/assets/images/icon.png',__FILE__ ),	
	        6
	    ); 
	}

	public function sm_news_ticker_menu_page(){
		echo "Hello News Ticker";
		$this->options = get_option( 'sm_news_ticker' );
		?>
		<form method="post" action="options.php">
			<?php settings_fields('sm_news_ticker_group_id');?>
			<?php do_settings_sections('sm-news-ticker');?>
			<?php submit_button();?>
		</form>
		<hr/>
        <div class="shortcode_info" style="background: #fff;padding: 10px 20px;">
        	<h2>Short Code:</h2>
        	<code>[sm-news-ticker]</code>
        </div>
		<?php
	}

	public function sm_news_ticker_init(){
		register_setting(
			'sm_news_ticker_group_id', // Option group
			'sm_news_ticker', //Option Name
			array($this,'serialize_fields') //Serialize
		);

		add_settings_section(
			'sm_settings_section',
			'SM News Ticker Settings',
			array($this,'sm_news_ticker_callback'),
			'sm-news-ticker'
		);

		add_settings_field(
			'title',
			'Title',
			array($this,'title_callback'),
			'sm-news-ticker',
			'sm_settings_section'
		);

		add_settings_field(
			'sm_post_type',
			'Select Post Type',
			array($this,'sm_post_type_callback'),
			'sm-news-ticker',
			'sm_settings_section'
		);


		add_settings_field(
			'sm_number_of_post',
			'Number of Post',
			array($this,'sm_number_of_post_callback'),
			'sm-news-ticker',
			'sm_settings_section'
		);

		add_settings_field(
			'controls',
			'News Ticker Control',
			array($this,'sm_controls_callback'),
			'sm-news-ticker',
			'sm_settings_section'
		);




	}

	public function serialize_fields($sm_news_tiker_input){
		$new_sm_news_ticker_input = array();

		if( isset( $sm_news_tiker_input['sm_post_type'] ) )
            $new_sm_news_ticker_input['sm_post_type'] = sanitize_text_field( $sm_news_tiker_input['sm_post_type'] );

        if( isset( $sm_news_tiker_input['title'] ) )
            $new_sm_news_ticker_input['title'] = sanitize_text_field( $sm_news_tiker_input['title'] );


        if( isset( $sm_news_tiker_input['sm_number_of_post'] ) )
            $new_sm_news_ticker_input['sm_number_of_post'] = absint( $sm_news_tiker_input['sm_number_of_post'] );

        if( isset( $sm_news_tiker_input['controls'] ) )
            $new_sm_news_ticker_input['controls'] = sanitize_text_field( $sm_news_tiker_input['controls'] );


        return $new_sm_news_ticker_input;
	}	

	public function sm_news_ticker_callback(){
		print "Enter Settings Bellow:";
	}

	public function sm_number_of_post_callback(){
		printf('<input type="text" name="sm_news_ticker[sm_number_of_post]" id="sm_number_of_post" value="%s"',
			isset( $this->options['sm_number_of_post'] ) ? esc_attr( $this->options['sm_number_of_post']) : 5
			);
	}

	public function title_callback(){
		printf('<input type="text" name="sm_news_ticker[title]" id="title" value="%s"',
			isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : "Latest News"
			);
	}

	/*public function sm_controls_callback(){
		printf('<input type="text" name="sm_news_ticker[controls]" id="title" value="%s"',
			isset( $this->options['controls'] ) ? esc_attr( $this->options['controls']) : "true"
			);
	}*/

	public function sm_controls_callback()
    {
        printf(
            '<input class="checkbox" type="checkbox" id="controls" '.checked(isset( $this->options['controls'] ),true,false).' name="sm_news_ticker[controls]" value="true" />',
            isset( $this->options['controls'] ) ? esc_attr( $this->options['controls']) : 'false'
        );
    }




	public function sm_post_type_callback(){

		$args = array(
		   'public'   => true
		);

		$output = 'objects'; // names or objects

		$post_types = get_post_types( $args, $output );
		unset($post_types['page']);
		unset($post_types['attachment']);
		$selected_post_type=(isset( $this->options['sm_post_type'] )) ? esc_attr( $this->options['sm_post_type']) : '';
		echo '<select id="sm_post_type" name="sm_news_ticker[sm_post_type]">';
		foreach ( $post_types  as $post_type ) {
			$selected= ($selected_post_type==$post_type->name)?' selected' : '';
			printf( "<option value='%s' %s>%s</option>\n", $post_type->name, $selected, $post_type->labels->name ); 
		}
		echo "</select>";

	}



	public function sm_news_ticker_shortcode($atts){
		$this->options = get_option( 'sm_news_ticker' );		
		$atts = extract(shortcode_atts( array(
			'post_type'=>$this->options['sm_post_type'],
			'count'=>$this->options['sm_number_of_post'],
			'title'=>$this->options['title'],
			'controls'=>$this->options['controls'],
		), $atts, 'sm-news-ticker' ));

		$sm_q=new WP_Query(array('posts_per_page'=>$count,'post_type'=>$post_type));

		echo '<ul id="js-news" class="js-hidden">';
				while ($sm_q->have_posts()): $sm_q->the_post();
					echo '<li class="news-item"><a href="'.get_the_permalink().'">'.get_the_title().'</a></li>';
				endwhile;
		echo '</ul>
				<script type="text/javascript">
					var SMjQ=jQuery.noConflict();
				    SMjQ(function () {
				        SMjQ("#js-news").ticker({
				        	speed: 0.10,           
					        ajaxFeed: false,       
					        feedUrl: false,   
					        feedType: "xml",       
					        htmlFeed: true,       
					        debugMode: true,  
					        controls: '.$controls.',        
					        titleText: "'.$title.'",   
					        displayType: "reveal",
					        direction: "ltr",       
					        pauseOnItems: 2000,    
					        fadeInSpeed: 600,      
					        fadeOutSpeed: 300      
				        });
				    });
				</script>
				';
	}


}

$sm_news_ticker = new SM_News_Ticker_Settings_Admin;