<?php
/**
 * Plugin Name: Facebook OpenGraph Protocol Post
 * Plugin URI: http://wordpress.org/#
 * Description: Adds the ability to add Facebook OpenGraph meta info to posts.
 * Author: Gavin Cooper
 * Version: 0.1
 * Author URI: http://gavincoop.co.uk/
 */

if (function_exists('add_action'))
{
	add_action('admin_init', 'facebook_init');
	
	function facebook_init()
	{
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain('facebook-og', null, $plugin_dir);
	
		if (function_exists('add_meta_box'))
		{
			add_meta_box('fbox', 'Facebook OpenGraph Meta', 'facebook_og', 'post', 'side', 'low');
			add_action('wp_insert_post', 'save_facebook_og', 10, 2);
		}
	}

	function save_facebook_og($post_id, $post = null)
	{
		if (!is_null($post))
		{
			$keys = get_facebook_og_attrs();

			foreach ($keys as $k)
			{
				// Attribute provided, save it.
				if (isset($_POST[$k]) && $_POST[$k] != '')
				{
					update_post_meta($post->ID, $k, $_POST[$k]);
				}
			}
		}
	}
	
	function get_facebook_og_attrs()
	{
		return array(
			'title',
			'img',
			'desc',
			'type'
		);
	}
	
	function facebook_og()
	{
		global $post;
		$custom = get_post_custom($post->ID);
		$a = get_facebook_og_attrs();
		
		$data = array();
		foreach ($a as $k)
		{
			$data[$k] = (isset($custom[$k]) && isset($custom[$k][0])) ? $custom[$k][0] : '';
		}
		
		// All supported types for OpenGraph Protocol {@see http://developers.facebook.com/docs/opengraph#types}.
		$types = array(
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
					foreach ($types as $t)
					{
						$s = ($data['type'] != '' && $data['type'] == $t) ? ' selected="selected"' : '';
						echo('<option value="'.$t.'"'.$s.'>'.ucwords(str_replace('_', ' ', $t)).'</option>'."\n");
					}
				?>
			</select>
		</p>
		
		<p>
			<label for=img>Image <em>(relative url)</em></label>
			<input type=text placeholder="Relative Image URL" name=img id=img value="<?php echo($data['img']); ?>" />
		</p>
		
		<p>
			<label for=desc>Description <em>(leave blank for excerpt)</em></label>
			<textarea placeholder=Optional name=desc id=desc><?php echo($data['desc']); ?></textarea>
		</p>
		<?php
	}
}

?>