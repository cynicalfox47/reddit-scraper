<?php
/**

	PHP reddit posts scraper by cynicalfox47.
	
	Website      : https://cynicalfox47.cf
	Project page : https://beta.cynicalfox47.cf/~reddit
	
**/
class reddit {
	private $subreddit;
	private $limit;
	private $posts;

	function get_posts($subreddit,$limit,$override) {

		$subreddit = $subreddit??'NaturePics';
		$limit = $limit??5;

		//Check for an override command
		if(!is_null($override)) {
			$posts = $this->get_reddit_posts($subreddit,$limit);
			unset($posts['meta']);
			return $posts;
		}

		//Check the age of the cache
		if($this->check_cache($subreddit,$limit)) {
			$posts = unserialize(file_get_contents("cache/".$subreddit));
			unset($posts['meta']); 
			return $posts;
		} else { 
			$posts = $this->get_reddit_posts($subreddit,$limit);
			unset($posts['meta']);
			return $posts;
		}
	}

	function check_cache($subreddit,$limit) {
		$cache = unserialize(file_get_contents("cache/".$subreddit));
		$sub = $cache['meta']['subreddit'];
		$age = time() - $cache['meta']['time'];

		$nposts = count($cache)-1;

		if (($age > 3600) or ($subreddit!==$sub) or $limit!==$nposts) {
			unset($age);
			return false;
		} else {
			unset($age);
			return true;
		}
	}
	function get_reddit_posts($subreddit,$limit) {
		
		$json = file_get_contents("https://www.reddit.com/r/".$subreddit."/.json?limit=".$limit);
		$posts = json_decode($json, true);

		$children = $posts['data']['children'];

		$posts = array();
		$i = 0;
		foreach ($children as $child){
			$posts[$i]['title'] = $child['data']['title'];
			$posts[$i]['date'] = $child['data']['created'];
			$posts[$i]['author'] = $child['data']['author'];
			$posts[$i]['img_url'] = $child['data']['preview']['images'][0]['source']['url'];
		    $posts[$i]['url'] = "https://www.reddit.com".$child['data']['permalink'];
		    if ($child['data']['stickied']==1) {
		    	unset($posts[$i]);
		    }
		    $i++;
		}

		/* ADD META */
		$posts['meta']['time'] = time();
		$posts['meta']['subreddit'] = $subreddit;
		file_put_contents("cache/".$subreddit, serialize($posts));

		return $posts;
	} //End of P.O.D f(x)
	function getCacheAge($subreddit) {
		$cache = unserialize(file_get_contents("cache/".$subreddit));		
		$age = time() - $cache['meta']['time'];

		$age = round($age/60);

		switch ($age) {
			case 0:
				return "Posts just recently cached";
				break;
			case 1:
				return "Posts were cached about a minute ago.";
				break;
			default:
				return "Posts were cached ~".$age." minutes ago.";
				break;
		}
	}
}
