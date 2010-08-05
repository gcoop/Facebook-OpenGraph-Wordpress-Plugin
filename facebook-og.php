<?php
/**
 * Plugin Name: Facebook OpenGraph Protocol Post
 * Plugin URI: http://wordpress.org/#
 * Description: Adds the ability to add Facebook OpenGraph meta info to posts.
 * Author: Gavin Cooper
 * Version: 0.1
 * Author URI: http://gavincoop.co.uk/
 */

class FacebookOG
{
	/**
	 * Attributes handled by meta box. Makes it easy to retrieve/insert the keys/values from storage.
	 * @var array
	 */
	private static $ATTRS = array(
		'title',
		'image',
		'desc',
		'type'
	);
	
	/**
	 * Supported content types by the Facebook OpenGraph Protocol {@seehttp://developers.facebook.com/docs/opengraph#types}.
	 * @var array
	 */
	private static $TYPES = array(
		'activity',
		'sport',
		'bar',
		'company',
		'cafe',
		'hotel',
		'resturant',
		'cause',
		'sports_league',
		'sports_team',
		'band',
		'goverment',
		'non_profit',
		'school',
		'university',
		'actor',
		'athlete',
		'author',
		'director',
		'musician',
		'politician',
		'public_figure',
		'city',
		'country',
		'landmark',
		'state_province',
		'album',
		'book',
		'drink',
		'food',
		'game',
		'product',
		'song',
		'movie',
		'tv_show',
		'blog',
		'article'
	);
	
	/**
	 * Construct adds the hooks.
	 */
	public function __construct()
	{
		add_action('admin_init', array($this, 'admin_init'));
		add_action('wp_head', array($this, 'meta'));
	}
	
	public function meta()
	{
		if (is_single())
		{
			global $post;
			$custom = get_post_custom($post->ID);
			
			foreach (self::$ATTRS as $k)
			{
				$v = (isset($custom[$k]) && isset($custom[$k][0])) ? $custom[$k][0] : null;
			
				if (!is_null($v))
					echo('<meta property="og:'.$k.'" content="'.$v.'" />'."\n");			
			}
		}
	}
	
	/**
	 * Allows you to register the Facebook OG meta box plugin for a new post type.
	 * 
	 * @param string 			$t					Custom post type ID.
	 */
	public function register_for_type($t)
	{
		if (function_exists('add_meta_box'))
		{
			add_meta_box('fbox', 'Facebook OpenGraph Meta', array($this, 'facebook_og'), $t, 'side', 'low');
			add_action('wp_insert_post', array($this, 'save'), 10, 2);
		}
	}
	
	/**
	 * Called by admin_init. Adds the meta boxes to the 'post' type.
	 */
	public function admin_init()
	{
		$this->register_for_type('post');
	}

	/**
	 * Iterates self::$ATTRS detecting updated information by the last post request to save the post and updates 
	 * attributes accordingly.
	 * 
	 * @param int 				$post_id			Post ID.
	 * @param WPPost|null		$post				Wordpress post object.
	 */
	public function save($post_id, $post = null)
	{
		if (!is_null($post))
		{
			foreach (self::$ATTRS as $k)
			{
				// Attribute provided, save it.
				if (isset($_POST[$k]) && $_POST[$k] != '')
				{
					update_post_meta($post->ID, $k, $_POST[$k]);
				}
			}
		}
	}
	
	/**
	 * Spits out the contents of our custom meta box.
	 */
	public function facebook_og()
	{
		global $post;
		$custom = get_post_custom($post->ID);
		
		$data = array();
		foreach (self::$ATTRS as $k)
		{
			$data[$k] = (isset($custom[$k]) && isset($custom[$k][0])) ? $custom[$k][0] : '';
		}
	
		?>
		<style>
			#fbox input,
			#fbox textarea
			{
				width:			99%;
			}
		</style>
		<input type=hidden name=myplugin_noncename value=<?php wp_create_nonce(plugin_basename(__FILE__)); ?> />
		<p>
			<label for=title>Post Title <em>(leave blank for post title)</em></label>
			<br />
			<input type=text placeholder=Optional name=title id=title value="<?php echo($data['title']); ?>" />
		</p>
		
		<p>
			<label for=type>Post Type</label>
			<br />
			<select id=type name=type>
				<?php
					foreach (self::$TYPES as $t)
					{
						$s = ($data['type'] != '' && $data['type'] == $t) ? ' selected="selected"' : '';
						echo('<option value="'.$t.'"'.$s.'>'.ucwords(str_replace('_', ' ', $t)).'</option>'."\n");
					}
				?>
			</select>
		</p>
		
		<p>
			<label for=image>Image <em>(relative url)</em></label>
			<input type=text placeholder="Relative Image URL" name=image id=image value="<?php echo($data['image']); ?>" />
		</p>
		
		<p>
			<label for=desc>Description <em>(leave blank for excerpt)</em></label>
			<textarea placeholder=Optional name=desc id=desc><?php echo($data['desc']); ?></textarea>
		</p>
		<?php
	}
}

/**
 * Add a init function to the init hook.
 */
add_action('init', 'facebook_og');

/**
 * Init function creates an instance of {@see FacebookOG} and puts it in a global var $facebook_og.
 */
function facebook_og() 
{ 
	static $facebook_og;
	
	if (!isset($facebook_og))
		$facebook_og = new FacebookOG(); 

	return $facebook_og;
}
?>