<?php
/*
 
 $Id: sitemap-core.php 419868 2011-08-05 18:57:00Z arnee $

*/

//Enable for dev! Good code doesn't generate any notices...
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/**
 * Represents the status (successes and failures) of a ping process
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0b5
 */
class GoogleSitemapGeneratorStatus {

	/**
	 * @var float $_startTime The start time of the building process
	 */
	private $startTime = 0;

	/**
	 * @var float $_endTime The end time of the building process
	 */
	private $endTime = 0;

	/**
	 * @var array Holding an array with the results and information of the last ping
	 */
	private $pingResults = array();

	/**
	 * Constructs a new status ued for saving the ping results
	 */
	public function __construct() {
		$this->startTime = microtime(true);

		$exists = get_option("sm_status");

		if($exists === false) add_option("sm_status", "", null, "no");

		$this->Save();
	}

	/**
	 * Saves the status back to the database
	 */
	public function Save() {
		update_option("sm_status", $this);
	}

	/**
	 * Returns the last saved status object or null
	 *
	 * @return GoogleSitemapGeneratorStatus
	 */
	public static function Load() {
		$status = @get_option("sm_status");
		if(is_a($status, "GoogleSitemapGeneratorStatus")) {
			return $status;
		}
		else return null;
	}

	/**
	 * Ends the ping process
	 */
	public function End() {
		$this->endTime = microtime(true);
		$this->Save();
	}

	/**
	 * Returns the duration of the ping process
	 * @return int
	 */
	public function GetDuration() {
		return round($this->endTime - $this->startTime, 2);
	}

	/**
	 * Returns the time when the pings were started
	 * @return int
	 */
	public function GetStartTime() {
		return round($this->startTime, 2);
	}

	/**
	 * @param  $service The internal name of the ping service
	 * @param  $url The URL to ping
	 * @param  $name The display name of the service
	 * @return void
	 */
	public function StartPing($service, $url, $name = null) {
		$this->pingResults[$service] = array(
			'startTime' => microtime(true),
			'endTime' => 0,
			'success' => false,
			'url' => $url,
			'name' => $name ? $name : $service
		);

		$this->Save();
	}

	/**
	 * @param  $service The internal name of the ping service
	 * @param  $success If the ping was successful
	 * @return void
	 */
	public function EndPing($service, $success) {
		$this->pingResults[$service]['endTime'] = microtime(true);
		$this->pingResults[$service]['success'] = $success;

		$this->Save();
	}

	/**
	 * Returns the duration of the last ping of a specific ping service
	 *
	 * @param  $service The internal name of the ping service
	 * @return float
	 */
	public function GetPingDuration($service) {
		$res = $this->pingResults[$service];
		return round($res['endTime'] - $res['startTime'], 2);
	}

	/**
	 * Returns the last result for a specific ping service
	 *
	 * @param  $service The internal name of the ping service
	 * @return array
	 */
	public function GetPingResult($service) {
		return $this->pingResults[$service]['success'];
	}

	/**
	 * Returns the URL for a specific ping service
	 *
	 * @param  $service The internal name of the ping service
	 * @return array
	 */
	public function GetPingUrl($service) {
		return $this->pingResults[$service]['url'];
	}

	/**
	 * Returns the name for a specific ping service
	 *
	 * @param  $service The internal name of the ping service
	 * @return array
	 */
	public function GetServiceName($service) {
		return $this->pingResults[$service]['name'];
	}

	/**
	 * Returns if a service was used in the last ping
	 *
	 * @param  $service The internal name of the ping service
	 * @return bool
	 */
	public function UsedPingService($service) {
		return array_key_exists($service, $this->pingResults);
	}

	/**
	 * Returns the services which were used in the last ping
	 *
	 * @return array
	 */
	public function GetUsedPingServices() {
		return array_keys($this->pingResults);
	}
}

/**
 * Represents an item in the page list
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPage {

	/**
	 * @var string $_url Sets the URL or the relative path to the blog dir of the page
	 */
	protected $_url;

	/**
	 * @var float $_priority Sets the priority of this page
	 */
	protected $_priority;

	/**
	 * @var string $_changeFreq Sets the chanfe frequency of the page. I want Enums!
	 */
	protected $_changeFreq;

	/**
	 * @var int $_lastMod Sets the lastMod date as a UNIX timestamp.
	 */
	protected $_lastMod;

	/**
	 * @var int $_postID Sets the post ID in case this item is a WordPress post or page
	 */
	protected $_postID;

	/**
	 * Initialize a new page object
	 *
	 * @since 3.0
	 * @param string $url The URL or path of the file
	 * @param float $priority The Priority of the page 0.0 to 1.0
	 * @param string $changeFreq The change frequency like daily, hourly, weekly
	 * @param int $lastMod The last mod date as a unix timestamp
	 * @param int $postID The post ID of this page
	 * @return GoogleSitemapGeneratorPage
	 *
	 */
	public function __construct($url = "", $priority = 0.0, $changeFreq = "never", $lastMod = 0, $postID = 0) {
		$this->SetUrl($url);
		$this->SetProprity($priority);
		$this->SetChangeFreq($changeFreq);
		$this->SetLastMod($lastMod);
		$this->SetPostID($postID);
	}

	/**
	 * Returns the URL of the page
	 *
	 * @return string The URL
	 */
	public function GetUrl() {
		return $this->_url;
	}

	/**
	 * Sets the URL of the page
	 *
	 * @param string $url The new URL
	 */
	public function SetUrl($url) {
		$this->_url = (string) $url;
	}

	/**
	 * Returns the priority of this page
	 *
	 * @return float the priority, from 0.0 to 1.0
	 */
	public function GetPriority() {
		return $this->_priority;
	}

	/**
	 * Sets the priority of the page
	 *
	 * @param float $priority The new priority from 0.1 to 1.0
	 */
	public function SetProprity($priority) {
		$this->_priority = floatval($priority);
	}

	/**
	 * Returns the change frequency of the page
	 *
	 * @return string The change frequncy like hourly, weekly, monthly etc.
	 */
	public function GetChangeFreq() {
		return $this->_changeFreq;
	}

	/**
	 * Sets the change frequency of the page
	 *
	 * @param string $changeFreq The new change frequency
	 */
	public function SetChangeFreq($changeFreq) {
		$this->_changeFreq = (string) $changeFreq;
	}

	/**
	 * Returns the last mod of the page
	 *
	 * @return int The lastmod value in seconds
	 */
	public function GetLastMod() {
		return $this->_lastMod;
	}

	/**
	 * Sets the last mod of the page
	 *
	 * @param int $lastMod The lastmod of the page
	 */
	public function SetLastMod($lastMod) {
		$this->_lastMod = intval($lastMod);
	}

	/**
	 * Returns the ID of the post
	 *
	 * @return int The post ID
	 */
	public function GetPostID() {
		return $this->_postID;
	}

	/**
	 * Sets the ID of the post
	 *
	 * @param int $postID The new ID
	 */
	public function SetPostID($postID) {
		$this->_postID = intval($postID);
	}

	public function Render() {

		if($this->_url == "/" || empty($this->_url)) return '';

		$r = "";
		$r .= "\t<url>\n";
		$r .= "\t\t<loc>" . $this->EscapeXML($this->_url) . "</loc>\n";
		if($this->_lastMod > 0) $r .= "\t\t<lastmod>" . date('Y-m-d\TH:i:s+00:00', $this->_lastMod) . "</lastmod>\n";
		if(!empty($this->_changeFreq)) $r .= "\t\t<changefreq>" . $this->_changeFreq . "</changefreq>\n";
		if($this->_priority !== false && $this->_priority !== "") $r .= "\t\t<priority>" . number_format($this->_priority, 1) . "</priority>\n";
		$r .= "\t</url>\n";
		return $r;
	}

	protected function EscapeXML($string) {
		return str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $string);
	}
}

/**
 * Represents an XML entry, like definitions
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorXmlEntry {

	protected $_xml;

	public function __construct($xml) {
		$this->_xml = $xml;
	}

	public function Render() {
		return $this->_xml;
	}
}

/**
 * Represents an comment
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 * @uses GoogleSitemapGeneratorXmlEntry
 */
class GoogleSitemapGeneratorDebugEntry extends GoogleSitemapGeneratorXmlEntry {

	public function Render() {
		return "<!-- " . $this->_xml . " -->\n";
	}
}

/**
 * Represents an item in the sitemap
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorSitemapEntry {

	/**
	 * @var string $_url Sets the URL or the relative path to the blog dir of the page
	 */
	protected $_url;

	/**
	 * @var int $_lastMod Sets the lastMod date as a UNIX timestamp.
	 */
	protected $_lastMod;

	/**
	 * Returns the URL of the page
	 *
	 * @return string The URL
	 */
	public function GetUrl() {
		return $this->_url;
	}

	/**
	 * Sets the URL of the page
	 *
	 * @param string $url The new URL
	 */
	public function SetUrl($url) {
		$this->_url = (string) $url;
	}

	/**
	 * Returns the last mod of the page
	 *
	 * @return int The lastmod value in seconds
	 */
	public function GetLastMod() {
		return $this->_lastMod;
	}

	/**
	 * Sets the last mod of the page
	 *
	 * @param int $lastMod The lastmod of the page
	 */
	public function SetLastMod($lastMod) {
		$this->_lastMod = intval($lastMod);
	}

	public function __construct($url = "", $lastMod = 0) {
		$this->SetUrl($url);
		$this->SetLastMod($lastMod);
	}

	public function Render() {

		if($this->_url == "/" || empty($this->_url)) return '';

		$r = "";
		$r .= "\t<sitemap>\n";
		$r .= "\t\t<loc>" . $this->EscapeXML($this->_url) . "</loc>\n";
		if($this->_lastMod > 0) $r .= "\t\t<lastmod>" . date('Y-m-d\TH:i:s+00:00', $this->_lastMod) . "</lastmod>\n";
		$r .= "\t</sitemap>\n";
		return $r;
	}

	protected function EscapeXML($string) {
		return str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $string);
	}
}

/**
 * Base class for all priority providers
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
abstract class GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * @var int $_totalComments The total number of comments of all posts
	 */
	protected $_totalComments = 0;

	/**
	 * @var int $_totalComments The total number of posts
	 */
	protected $_totalPosts = 0;

	/**
	 * Initializes a new priority provider
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 */
	public function __construct($totalComments, $totalPosts) {
		$this->_totalComments = $totalComments;
		$this->_totalPosts = $totalPosts;

	}

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated name
	 */
	public abstract function GetName();

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated description
	 */
	public abstract function GetDescription();

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @return int The calculated priority
	 */
	public abstract function GetPostPriority($postID, $commentCount);
}

/**
 * Priority Provider which calculates the priority based on the number of comments
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByCountProvider extends GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated name
	 */
	public function GetName() {
		return __("Comment Count", 'sitemap');
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated description
	 */
	public function GetDescription() {
		return __("Uses the number of comments of the post to calculate the priority", 'sitemap');
	}

	/**
	 * Initializes a new priority provider which calculates the post priority based on the number of comments
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 */
	public function __construct($totalComments, $totalPosts) {
		parent::__construct($totalComments, $totalPosts);
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @return int The calculated priority
	 */
	public function GetPostPriority($postID, $commentCount) {
		$prio = 0;
		if($this->_totalComments > 0 && $commentCount > 0) {
			$prio = round(($commentCount * 100 / $this->_totalComments) / 100, 1);
		} else {
			$prio = 0;
		}
		return $prio;
	}
}

/**
 * Priority Provider which calculates the priority based on the average number of comments
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByAverageProvider extends GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * @var int $_average The average number of comments per post
	 */
	protected $_average = 0.0;

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated name
	 */
	public function GetName() {
		return __("Comment Average", 'sitemap');
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated description
	 */
	public function GetDescription() {
		return __("Uses the average comment count to calculate the priority", 'sitemap');
	}

	/**
	 * Initializes a new priority provider which calculates the post priority based on the average number of comments
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 */
	public function __construct($totalComments, $totalPosts) {
		parent::__construct($totalComments, $totalPosts);

		if($this->_totalComments > 0 && $this->_totalPosts > 0) {
			$this->_average = (double) $this->_totalComments / $this->_totalPosts;
		}
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @return int The calculated priority
	 */
	public function GetPostPriority($postID, $commentCount) {
		$prio = 0;
		//Do not divide by zero!
		if($this->_average == 0) {
			if($commentCount > 0) {
				$prio = 1;
			}
			else $prio = 0;
		} else {
			$prio = $commentCount / $this->_average;
			if($prio > 1) {
				$prio = 1;
			}
			else if($prio < 0) $prio = 0;
		}

		return round($prio, 1);
	}
}

/**
 * Priority Provider which calculates the priority based on the popularity by the PopularityContest Plugin
 * @author Arne Brachhold
 * @package sitemap
 * @since 3.0
 */
class GoogleSitemapGeneratorPrioByPopularityContestProvider extends GoogleSitemapGeneratorPrioProviderBase {

	/**
	 * Returns the (translated) name of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated name
	 */
	public function GetName() {
		return __("Popularity Contest", 'sitemap');
	}

	/**
	 * Returns the (translated) description of this priority provider
	 *
	 * @since 3.0
	 * @return string The translated description
	 */
	public function GetDescription() {
		return str_replace("%4", "index.php?page=popularity-contest.php", str_replace("%3", "options-general.php?page=popularity-contest.php", str_replace("%2", "http://www.alexking.org/", str_replace("%1", "http://www.alexking.org/index.php?content=software/wordpress/content.php", __("Uses the activated <a href=\"%1\">Popularity Contest Plugin</a> from <a href=\"%2\">Alex King</a>. See <a href=\"%3\">Settings</a> and <a href=\"%4\">Most Popular Posts</a>", 'sitemap')))));
	}

	/**
	 * Initializes a new priority provider which calculates the post priority based on the popularity by the PopularityContest Plugin
	 *
	 * @param $totalComments int The total number of comments of all posts
	 * @param $totalPosts int The total number of posts
	 * @since 3.0
	 */
	public function __construct($totalComments, $totalPosts) {
		parent::__construct($totalComments, $totalPosts);
	}

	/**
	 * Returns the priority for a specified post
	 *
	 * @param $postID int The ID of the post
	 * @param $commentCount int The number of comments for this post
	 * @since 3.0
	 * @return int The calculated priority
	 */
	public function GetPostPriority($postID, $commentCount) {
		//$akpc is the global instance of the Popularity Contest Plugin
		global $akpc, $posts;

		$res = 0;
		//Better check if its there
		if(!empty($akpc) && is_object($akpc)) {
			//Is the method we rely on available?
			if(method_exists($akpc, "get_post_rank")) {
				if(!is_array($posts) || !$posts) $posts = array();
				if(!isset($posts[$postID])) $posts[$postID] = get_post($postID);
				//popresult comes as a percent value
				$popresult = $akpc->get_post_rank($postID);
				if(!empty($popresult) && strpos($popresult, "%") !== false) {
					//We need to parse it to get the priority as an int (percent)
					$matches = null;
					preg_match("/([0-9]{1,3})\%/si", $popresult, $matches);
					if(!empty($matches) && is_array($matches) && count($matches) == 2) {
						//Divide it so 100% = 1, 10% = 0.1
						$res = round(intval($matches[1]) / 100, 1);
					}
				}
			}
		}
		return $res;
	}
}

/**
 * Class to generate a sitemaps.org Sitemaps compliant sitemap of a WordPress blog.
 *
 * @package sitemap
 * @author Arne Brachhold
 * @since 3.0
 */
final class GoogleSitemapGenerator {
	/**
	 * @var array The unserialized array with the stored options
	 */
	private $options = array();

	/**
	 * @var array The saved additional pages
	 */
	private $pages = array();

	/**
	 * @var array The values and names of the change frequencies
	 */
	private $freqNames = array();

	/**
	 * @var array A list of class names which my be called for priority calculation
	 */
	private $prioProviders = array();

	/**
	 * @var bool True if init complete (options loaded etc)
	 */
	private $isInitiated = false;

	/**
	 * @var bool Defines if the sitemap building process is active at the moment
	 */
	private $isActive = false;

	/**
	 * @var array Holds options like output format and compression for the current request
	 */
	private $buildOptions = array();

	/**
	 * Holds the user interface object
	 *
	 * @since 3.1.1
	 * @var GoogleSitemapGeneratorUI
	 */
	private $ui = null;

	/**
	 * Defines if the simulation mode is on. In this case, data is not echoed but saved instead.
	 * @var boolean
	 */
	private $simMode = false;

	/**
	 * Holds the data if simulation mode is on
	 * @var array
	 */
	private $simData = array("sitemaps" => array(), "content" => array());


	/*************************************** CONSTRUCTION AND INITIALIZING ***************************************/

	/**
	 * Initializes a new Google Sitemap Generator
	 *
	 * @since 4.0
	 */
	private function __construct() {

	}

	/**
	 * Returns the instance of the Sitemap Generator
	 *
	 * @since 3.0
	 * @return GoogleSitemapGenerator The instance or null if not available.
	 */
	public static function GetInstance() {
		if(isset($GLOBALS["sm_instance"])) {
			return $GLOBALS["sm_instance"];
		} else return null;
	}

	/**
	 * Enables the Google Sitemap Generator and registers the WordPress hooks
	 *
	 * @since 3.0
	 */
	public function Enable() {
		if(!isset($GLOBALS["sm_instance"])) {
			$GLOBALS["sm_instance"] = new GoogleSitemapGenerator();
		}
	}

	/**
	 * Loads up the configuration and validates the prioity providers
	 *
	 * This method is only called if the sitemaps needs to be build or the admin page is displayed.
	 *
	 * @since 3.0
	 */
	public function Initate() {
		if(!$this->isInitiated) {

			//Loading language file...
			//load_plugin_textdomain('sitemap');
			//Hmm, doesn't work if the plugin file has its own directory.
			//Let's make it our way... load_plugin_textdomain() searches only in the wp-content/plugins dir.
			$currentLocale = get_locale();
			if(!empty($currentLocale)) {
				$moFile = dirname(__FILE__) . "/lang/sitemap-" . $currentLocale . ".mo";
				if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('sitemap', $moFile);
			}

			$this->freqNames = array(
				"always" => __("Always", "sitemap"),
				"hourly" => __("Hourly", "sitemap"),
				"daily" => __("Daily", "sitemap"),
				"weekly" => __("Weekly", "sitemap"),
				"monthly" => __("Monthly", "sitemap"),
				"yearly" => __("Yearly", "sitemap"),
				"never" => __("Never", "sitemap")
			);


			$this->LoadOptions();
			$this->LoadPages();

			//Register our own priority providers
			add_filter("sm_add_prio_provider", array($this, 'AddDefaultPrioProviders'));

			//Let other plugins register their providers
			$r = apply_filters("sm_add_prio_provider", $this->prioProviders);

			//Check if no plugin return null
			if($r != null) $this->prioProviders = $r;

			$this->ValidatePrioProviders();

			$this->isInitiated = true;
		}
	}


	/*************************************** VERSION AND LINK HELPERS ***************************************/

	/**
	 * Returns the version of the generator
	 *
	 * @since 3.0
	 * @return int The version
	 */
	public static function GetVersion() {
		return GoogleSitemapGeneratorLoader::GetVersion();
	}

	/**
	 * Returns the SVN version of the generator
	 *
	 * @since 4.0
	 * @return string The SVN version string
	 */
	public static function GetSvnVersion() {
		return GoogleSitemapGeneratorLoader::GetSvnVersion();
	}

	/**
	 * Returns a link pointing to a specific page of the authors website
	 *
	 * @since 3.0
	 * @param $redir The to link to
	 * @return string The full url
	 */
	public static function GetRedirectLink($redir) {
		return trailingslashit("http://www.arnebrachhold.de/redir/" . $redir);
	}

	/**
	 * Returns a link pointing back to the plugin page in WordPress
	 *
	 * @since 3.0
	 * @return string The full url
	 */
	public static function GetBackLink() {
		global $wp_version;
		$url = admin_url("options-general.php?page=" . GoogleSitemapGeneratorLoader::GetBaseName());

		//Some browser cache the page... great! So lets add some no caching params depending on the WP and plugin version
		$url .= '&sm_wpv=' . $wp_version . '&sm_pv=' . GoogleSitemapGeneratorLoader::GetVersion();

		return $url;
	}

	/**
	 * Converts a mysql datetime value into a unix timestamp
	 * @param $mysqlDateTime The in the mysql datetime format
	 * @return int The time in seconds
	 */
	public static function GetTimestampFromMySql($mysqlDateTime) {
		list($date, $hours) = explode(' ', $mysqlDateTime);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $min, $sec) = explode(':', $hours);
		return mktime(intval($hour), intval($min), intval($sec), intval($month), intval($day), intval($year));
	}


	/*************************************** SIMPLE GETTERS ***************************************/

	/**
	 * Returns the names for the frequency values
	 * @return array
	 */
	public function GetFreqNames() {
		return $this->freqNames;
	}

	/**
	 * Returns if the blog is running in multi site mode
	 * @since 4.0
	 * @return bool
	 */
	public function IsMultiSite() {
		return (function_exists("is_multisite") && is_multisite());
	}

	/**
	 * Returns if the sitemap building process is currently active
	 *
	 * @since 3.0
	 * @return bool true if active
	 */
	public function IsActive() {
		$inst = GoogleSitemapGenerator::GetInstance();
		return ($inst != null && $inst->isActive);
	}

	/**
	 * Returns if the compressed sitemap was activated
	 *
	 * @since 3.0b8
	 * @return true if compressed
	 */
	public function IsGzipEnabled() {
		return ($this->GetOption("b_gzip") === true && function_exists("gzwrite"));
	}

	/**
	 * Returns if the XML Dom and XSLT functions are enabled
	 *
	 * @since 4.0b1
	 * @return true if compressed
	 */
	public function IsXslEnabled() {
		return (class_exists("DomDocument") && class_exists("XSLTProcessor"));
	}


	/*************************************** TAXONOMIES AND CUSTOM POST TYPES ***************************************/

	/**
	 * Returns if this version of WordPress supports the new taxonomy system
	 *
	 * @since 3.0b8
	 * @return true if supported
	 */
	public function IsTaxonomySupported() {
		return (function_exists("get_taxonomy") && function_exists("get_terms") && function_exists("get_taxonomies"));
	}

	/**
	 * Returns the list of custom taxonomies. These are basically all taxonomies without categories and post tags
	 *
	 * @since 3.1.7
	 * @return array Array of names of user-defined taxonomies
	 */
	public function GetCustomTaxonomies() {
		$taxonomies = get_taxonomies(array("public" => 1));
		return array_diff($taxonomies, array("category", "post_tag", "nav_menu", "link_category", "post_format"));
	}

	/**
	 * Returns if this version of WordPress supports custom post types
	 *
	 * @since 3.2.5
	 * @return true if supported
	 */
	public function IsCustomPostTypesSupported() {
		return (function_exists("get_post_types") && function_exists("register_post_type"));
	}

	/**
	 * Returns the list of custom post types. These are all custom post types except post, page and attachment
	 *
	 * @since 3.2.5
	 * @return array Array of custom post types as per get_post_types
	 */
	public function GetCustomPostTypes() {
		$post_types = get_post_types(array("public" => 1));
		$post_types = array_diff($post_types, array("post", "page", "attachment"));
		return $post_types;
	}


	/**
	 * Returns the list of active post types, built-in and custom ones.
	 *
	 * @since 4.0b5
	 * @return array Array of custom post types as per get_post_types
	 */
	public function GetActivePostTypes() {
		$allPostTypes = get_post_types();
		$enabledPostTypes = $this->GetOption('in_customtypes');
		if($this->GetOption("in_posts")) $enabledPostTypes[] = "post";
		if($this->GetOption("in_pages")) $enabledPostTypes[] = "page";

		$activePostTypes = array();
		foreach($enabledPostTypes AS $postType) {
			if(!empty($postType) && in_array($postType, $allPostTypes)) {
				$activePostTypes[] = $postType;
			}
		}
		return $activePostTypes;
	}


	/*************************************** PRIORITY PROVIDERS ***************************************/

	/**
	 * Returns the list of PriorityProviders
	 * @return array
	 */
	public function GetPrioProviders() {
		return $this->prioProviders;
	}

	/**
	 * Adds the default Priority Providers to the provider list
	 *
	 * @since 3.0
	 * @param $providers
	 * @return array
	 */
	public function AddDefaultPrioProviders($providers) {
		array_push($providers, "GoogleSitemapGeneratorPrioByCountProvider");
		array_push($providers, "GoogleSitemapGeneratorPrioByAverageProvider");
		if(class_exists("ak_popularity_contest")) {
			array_push($providers, "GoogleSitemapGeneratorPrioByPopularityContestProvider");
		}
		return $providers;
	}

	/**
	 * Validates all given Priority Providers by checking them for required methods and existence
	 *
	 * @since 3.0
	 */
	private function ValidatePrioProviders() {
		$validProviders = array();

		for($i = 0; $i < count($this->prioProviders); $i++) {
			if(class_exists($this->prioProviders[$i])) {
				if(is_subclass_of($this->prioProviders[$i], "GoogleSitemapGeneratorPrioProviderBase")) {
					array_push($validProviders, $this->prioProviders[$i]);
				}
			}
		}
		$this->prioProviders = $validProviders;

		if(!$this->GetOption("b_prio_provider")) {
			if(!in_array($this->GetOption("b_prio_provider"), $this->prioProviders, true)) {
				$this->SetOption("b_prio_provider", "");
			}
		}
	}


	/*************************************** COMMENT HANDLING FOR PRIO. PROVIDERS ***************************************/

	/**
	 * Retrieves the number of comments of a post in a asso. array
	 * The key is the postID, the value the number of comments
	 *
	 * @since 3.0
	 * @return array An array with postIDs and their comment count
	 */
	public function GetComments() {
		global $wpdb;
		$comments = array();

		//Query comments and add them into the array
		$commentRes = $wpdb->get_results("SELECT `comment_post_ID` as `post_id`, COUNT(comment_ID) as `comment_count` FROM `" . $wpdb->comments . "` WHERE `comment_approved`='1' GROUP BY `comment_post_ID`");
		if($commentRes) {
			foreach($commentRes as $comment) {
				$comments[$comment->post_id] = $comment->comment_count;
			}
		}
		return $comments;
	}

	/**
	 * Calculates the full number of comments from an sm_getComments() generated array
	 *
	 * @since 3.0
	 * @param $comments array The Array with posts and c0mment count
	 * @see sm_getComments
	 * @return The full number of comments
	 */
	public function GetCommentCount($comments) {
		$commentCount = 0;
		foreach($comments AS $k => $v) {
			$commentCount += $v;
		}
		return $commentCount;
	}


	/*************************************** OPTION HANDLING ***************************************/

	/**
	 * Sets up the default configuration
	 *
	 * @since 3.0
	 */
	public function InitOptions() {

		$this->options = array();
		$this->options["sm_b_prio_provider"] = "GoogleSitemapGeneratorPrioByCountProvider"; //Provider for automatic priority calculation
		$this->options["sm_b_ping"] = true; //Auto ping Google
		$this->options["sm_b_pingyahoo"] = false; //Auto ping YAHOO
		$this->options["sm_b_yahookey"] = ''; //YAHOO Application Key
		$this->options["sm_b_pingask"] = true; //Auto ping Ask.com
		$this->options["sm_b_pingmsn"] = true; //Auto ping MSN
		$this->options["sm_b_memory"] = ''; //Set Memory Limit (e.g. 16M)
		$this->options["sm_b_time"] = -1; //Set time limit in seconds, 0 for unlimited, -1 for disabled
		$this->options["sm_b_style_default"] = true; //Use default style
		$this->options["sm_b_style"] = ''; //Include a stylesheet in the XML
		$this->options["sm_b_robots"] = true; //Add sitemap location to WordPress' virtual robots.txt file
		$this->options["sm_b_html"] = true; //Include a link to a html version of the sitemap in the XML sitemap
		$this->options["sm_b_exclude"] = array(); //List of post / page IDs to exclude
		$this->options["sm_b_exclude_cats"] = array(); //List of post / page IDs to exclude

		$this->options["sm_in_home"] = true; //Include homepage
		$this->options["sm_in_posts"] = true; //Include posts
		$this->options["sm_in_posts_sub"] = false; //Include post pages (<!--nextpage--> tag)
		$this->options["sm_in_pages"] = true; //Include static pages
		$this->options["sm_in_cats"] = false; //Include categories
		$this->options["sm_in_arch"] = false; //Include archives
		$this->options["sm_in_auth"] = false; //Include author pages
		$this->options["sm_in_tags"] = false; //Include tag pages
		$this->options["sm_in_tax"] = array(); //Include additional taxonomies
		$this->options["sm_in_customtypes"] = array(); //Include custom post types
		$this->options["sm_in_lastmod"] = true; //Include the last modification date

		$this->options["sm_cf_home"] = "daily"; //Change frequency of the homepage
		$this->options["sm_cf_posts"] = "monthly"; //Change frequency of posts
		$this->options["sm_cf_pages"] = "weekly"; //Change frequency of static pages
		$this->options["sm_cf_cats"] = "weekly"; //Change frequency of categories
		$this->options["sm_cf_auth"] = "weekly"; //Change frequency of author pages
		$this->options["sm_cf_arch_curr"] = "daily"; //Change frequency of the current archive (this month)
		$this->options["sm_cf_arch_old"] = "yearly"; //Change frequency of older archives
		$this->options["sm_cf_tags"] = "weekly"; //Change frequency of tags

		$this->options["sm_pr_home"] = 1.0; //Priority of the homepage
		$this->options["sm_pr_posts"] = 0.6; //Priority of posts (if auto prio is disabled)
		$this->options["sm_pr_posts_min"] = 0.2; //Minimum Priority of posts, even if autocalc is enabled
		$this->options["sm_pr_pages"] = 0.6; //Priority of static pages
		$this->options["sm_pr_cats"] = 0.3; //Priority of categories
		$this->options["sm_pr_arch"] = 0.3; //Priority of archives
		$this->options["sm_pr_auth"] = 0.3; //Priority of author pages
		$this->options["sm_pr_tags"] = 0.3; //Priority of tags

		$this->options["sm_i_donated"] = false; //Did you donate? Thank you! :)
		$this->options["sm_i_hide_donated"] = false; //And hide the thank you..
		$this->options["sm_i_install_date"] = time(); //The installation date
		$this->options["sm_i_hide_note"] = false; //Hide the note which appears after 30 days
		$this->options["sm_i_hide_works"] = false; //Hide the "works?" message which appears after 15 days
		$this->options["sm_i_hide_donors"] = false; //Hide the list of donations
	}

	/**
	 * Loads the configuration from the database
	 *
	 * @since 3.0
	 */
	private function LoadOptions() {

		$this->InitOptions();

		//First init default values, then overwrite it with stored values so we can add default
		//values with an update which get stored by the next edit.
		$storedOptions = get_option("sm_options");
		if($storedOptions && is_array($storedOptions)) {
			foreach($storedOptions AS $k => $v) {
				$this->options[$k] = $v;
			}
		} else update_option("sm_options", $this->options); //First time use, store default values
	}

	/**
	 * Returns the option value for the given key
	 *
	 * @since 3.0
	 * @param $key string The Configuration Key
	 * @return mixed The value
	 */
	public function GetOption($key) {
		$key = "sm_" . $key;
		if(array_key_exists($key, $this->options)) {
			return $this->options[$key];
		} else return null;
	}

	public function GetOptions() {
		return $this->options;
	}

	/**
	 * Sets an option to a new value
	 *
	 * @since 3.0
	 * @param $key string The configuration key
	 * @param $value mixed The new object
	 */
	public function SetOption($key, $value) {
		if(strpos($key, "sm_") !== 0) $key = "sm_" . $key;

		$this->options[$key] = $value;
	}

	/**
	 * Saves the options back to the database
	 *
	 * @since 3.0
	 * @return bool true on success
	 */
	public function SaveOptions() {
		$oldvalue = get_option("sm_options");
		if($oldvalue == $this->options) {
			return true;
		} else return update_option("sm_options", $this->options);
	}

	/**
	 * Returns the additional pages
	 * @since 4.0
	 * @return array
	 */
	function GetPages() {
		return $this->pages;
	}

	/**
	 * Returns the additional pages
	 * @since 4.0
	 * @param array $pages
	 */
	function SetPages(array $pages) {
		$this->pages = $pages;
	}

	/**
	 * Loads the stored pages from the database
	 *
	 * @since 3.0
	 */
	private function LoadPages() {
		global $wpdb;

		$needsUpdate = false;

		$pagesString = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'sm_cpages'");

		//Class sm_page was renamed with 3.0 -> rename it in serialized value for compatibility
		if(!empty($pagesString) && strpos($pagesString, "sm_page") !== false) {
			$pagesString = str_replace("O:7:\"sm_page\"", "O:26:\"GoogleSitemapGeneratorPage\"", $pagesString);
			$needsUpdate = true;
		}

		if(!empty($pagesString)) {
			$storedpages = unserialize($pagesString);
			$this->pages = $storedpages;
		} else {
			$this->pages = array();
		}

		if($needsUpdate) $this->SavePages();
	}

	/**
	 * Saved the additional pages back to the database
	 *
	 * @since 3.0
	 * @return true on success
	 */
	public function SavePages() {
		$oldvalue = get_option("sm_cpages");
		if($oldvalue == $this->pages) {
			return true;
		} else {
			delete_option("sm_cpages");
			//Add the option, Note the autoload=false because when the autoload happens, our class GoogleSitemapGeneratorPage doesn't exist
			add_option("sm_cpages", $this->pages, null, "no");
			return true;
		}
	}


	/*************************************** URL AND PATH FUNCTIONS ***************************************/

	/**
	 * Returns the URL to the directory where the plugin file is located
	 * @since 3.0b5
	 * @return string The URL to the plugin directory
	 */
	public function GetPluginUrl() {

		//Try to use WP API if possible, introduced in WP 2.6
		if(function_exists('plugins_url')) return trailingslashit(plugins_url(basename(dirname(__FILE__))));

		//Try to find manually... can't work if wp-content was renamed or is redirected
		$path = dirname(__FILE__);
		$path = str_replace("\\", "/", $path);
		$path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path, strpos($path, "wp-content/")));
		return $path;
	}

	/**
	 * Returns the path to the directory where the plugin file is located
	 * @since 3.0b5
	 * @return string The path to the plugin directory
	 */
	public function GetPluginPath() {
		$path = dirname(__FILE__);
		return trailingslashit(str_replace("\\", "/", $path));
	}

	/**
	 * Returns the URL to default XSLT style if it exists
	 * @since 3.0b5
	 * @return string The URL to the default stylesheet, empty string if not available.
	 */
	public function GetDefaultStyle() {
		$p = $this->GetPluginPath();
		if(file_exists($p . "sitemap.xsl")) {
			$url = $this->GetPluginUrl();
			//If called over the admin area using HTTPS, the stylesheet would also be https url, even if the blog frontend is not.
			if(substr(get_bloginfo('url'), 0, 5) != "https" && substr($url, 0, 5) == "https") $url = "http" . substr($url, 5);
			return $url . 'sitemap.xsl';
		}
		return '';
	}

	/**
	 * Returns the URL for the sitemap file
	 *
	 * @since 3.0
	 * @param string $type
	 * @param string $params
	 * @param array $buildOptions
	 * @return The URL to the Sitemap file
	 */
	public function GetXmlUrl($type = "", $params = "", $buildOptions = array()) {
		global $wp_rewrite;

		$pl = $wp_rewrite->using_mod_rewrite_permalinks();
		$options = "";
		if(!empty($type)) {
			$options .= $type;
			if(!empty($params)) {
				$options .= "-" . $params;
			}
		}

		$buildOptions = array_merge($this->buildOptions, $buildOptions);

		$html = (isset($buildOptions["html"]) ? $buildOptions["html"] : false);

		if($pl) {
			return trailingslashit(get_bloginfo('url')) . "sitemap" . ($options ? "-" . $options : "") . ($html
					? ".html" : ".xml");
		} else {
			return trailingslashit(get_bloginfo('url')) . "index.php?xml_sitemap=params=" . $options . ($html
					? ";html=true" : "");
		}
	}

	/**
	 * Returns if there is still an old sitemap file in the blog directory
	 *
	 * @return Boolean True if a sitemap file still exists
	 */
	public function OldFileExists() {
		$path = trailingslashit(get_home_path());
		return (file_exists($path . "sitemap.xml") || file_exists($path . "sitemap.xml.gz"));
	}

	/**
	 * Renames old sitemap files in the blog directory from previous versions of this plugin
	 * @return bool True on success
	 */
	public function DeleteOldFiles() {
		$path = trailingslashit(get_home_path());

		$res = true;

		if(file_exists($f = $path . "sitemap.xml")) if(!rename($f, $f . ".bak")) $res = false;
		if(file_exists($f = $path . "sitemap.xml.gz")) if(!rename($f, $f . ".bak")) $res = false;

		return $res;
	}


	/*************************************** SITEMAP SIMULATION ***************************************/

	/**
	 * Simulates the building of the sitemap index file.
	 *
	 * @see GoogleSitemapGenerator::SimulateSitemap
	 * @since 4.0
	 * @return array The data of the sitemap index file
	 */
	public function SimulateIndex() {

		$this->simMode = true;

		require_once(trailingslashit(dirname(__FILE__)) . "sitemap-builder.php");
		do_action("sm_build_index", $this);

		$this->simMode = false;

		$r = $this->simData["sitemaps"];

		$this->ClearSimData("sitemaps");

		return $r;
	}

	/**
	 * Simulates the building of the sitemap file.
	 *
	 * @see GoogleSitemapGenerator::SimulateIndex
	 * @since 4.0
	 * @param $type string The type of the sitemap
	 * @param $params string Additional parameters for this type
	 * @return array The data of the sitemap file
	 */
	public function SimulateSitemap($type, $params) {
		$this->simMode = true;

		require_once(trailingslashit(dirname(__FILE__)) . "sitemap-builder.php");
		do_action("sm_build_content", $this, $type, $params);

		$this->simMode = false;

		$r = $this->simData["content"];

		$this->ClearSimData("content");

		return $r;
	}

	/**
	 * Clears the data of the simulation
	 *
	 * @param string $what Defines what to clear, either both, sitemaps or content
	 * @see GoogleSitemapGenerator::SimulateIndex
	 * @see GoogleSitemapGenerator::SimulateSitemap
	 * @since 4.0
	 */
	public function ClearSimData($what) {
		if($what == "both" || $what == "sitemaps") {
			$this->simData["sitemaps"] = array();
		}

		if($what == "both" || $what == "content") {
			$this->simData["content"] = array();
		}
	}

	/**
	 * Returns the first caller outside of this __CLASS__
	 * @param array $trace The backtrace
	 * @return array The caller information
	 */
	private function GetExternalBacktrace($trace) {
		$caller = null;
		foreach($trace AS $b) {
			if($b["class"] != __CLASS__) {
				$caller = $b;
				break;
			}
		}
		return $caller;
	}


	/*************************************** SITEMAP BUILDING ***************************************/

	/**
	 * Shows the sitemap. Main etry point from HTTP
	 * @param string $options Options for the sitemap. What type, what parameters.
	 * @since 4.0
	 */
	public function ShowSitemap($options) {

		$startTime = microtime(true);
		$startQueries = $GLOBALS["wpdb"]->num_queries;
		$startMemory = memory_get_peak_usage(true);

		//Raise memory and time limits
		if($this->GetOption("b_memory") != '') {
			@ini_set("memory_limit", $this->GetOption("b_memory"));
		}

		if($this->GetOption("b_time") != -1) {
			@set_time_limit($this->GetOption("b_time"));
		}

		do_action("sm_init", $this);

		$this->isActive = true;

		$parsedOptions = array();

		$options = explode(";", $options);
		foreach($options AS $k) {
			$kv = explode("=", $k);
			$parsedOptions[$kv[0]] = @$kv[1];
		}

		$options = $parsedOptions;

		$this->buildOptions = $options;


		$pack = (isset($options['zip']) ? $options['zip'] : true);
		if(empty($_SERVER['HTTP_ACCEPT_ENCODING']) || strpos('gzip', $_SERVER['HTTP_ACCEPT_ENCODING']) === NULL || !$this->IsGzipEnabled() || headers_sent()) $pack = false;
		if($pack) ob_start('ob_gzhandler');

		$this->Initate();

		$builders = array('sitemap-builder.php'); //,"sitemap-builder-cpt.php");
		foreach($builders AS $b) {
			$f = trailingslashit(dirname(__FILE__)) . $b;
			if(file_exists($f)) require_once($f);
		}

		$html = (isset($options["html"]) ? $options["html"] : false) && $this->IsXslEnabled();

		if($html) {
			ob_start();
		} else {
			header('Content-Type: application/xml; charset=utf-8');
		}


		if(empty($options["params"]) || $options["params"] == "index") {

			$this->BuildSitemapHeader("index");

			do_action('sm_build_index', $this);

			$this->BuildSitemapFooter("index");
			$this->AddEndCommend($startTime, $startQueries, $startMemory);


		} else {
			$allParams = $options["params"];
			$type = $params = null;
			if(strpos($allParams, "-") !== false) {
				$type = substr($allParams, 0, strpos($allParams, "-"));
				$params = substr($allParams, strpos($allParams, "-") + 1);
			} else {
				$type = $allParams;
			}

			$this->BuildSitemapHeader("sitemap");

			do_action("sm_build_content", $this, $type, $params);

			$this->BuildSitemapFooter("sitemap");

			$this->AddEndCommend($startTime, $startQueries, $startMemory);
		}

		if($html) {
			$xmlSource = ob_get_clean();

			// Load the XML source
			$xml = new DOMDocument;
			$xml->loadXML($xmlSource);

			$xsl = new DOMDocument;
			$xsl->load($this->GetPluginPath() . "sitemap.xsl");

			// Configure the transformer
			$proc = new XSLTProcessor;
			$proc->importStyleSheet($xsl); // attach the xsl rules

			$domTranObj = $proc->transformToDoc($xml);

			// this will also output doctype and comments at top level
			foreach($domTranObj->childNodes as $node) echo $domTranObj->saveXML($node) . "\n";
		}

		if($pack) ob_end_flush();
		$this->isActive = false;
		exit;
	}

	/**
	 * Generates the header for the sitemap with XML declarations, stylesheet and so on.
	 *
	 * @since 4.0
	 * @param string $format The format, either sitemap for a sitemap or index for the sitemap index
	 */
	private function BuildSitemapHeader($format) {

		if(!in_array($format, array("sitemap", "index"))) $format = "sitemap";

		$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<?xml version="1.0" encoding="UTF-8"' . '?' . '>'));

		$styleSheet = ($this->GetDefaultStyle() && $this->GetOption('b_style_default') === true
				? $this->GetDefaultStyle() : $this->GetOption('b_style'));

		if(!empty($styleSheet)) {
			$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<' . '?xml-stylesheet type="text/xsl" href="' . $styleSheet . '"?' . '>'));
		}

		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("generator=\"wordpress/" . get_bloginfo('version') . "\""));
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("sitemap-generator-url=\"http://www.arnebrachhold.de\" sitemap-generator-version=\"" . $this->GetVersion() . "\""));
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("generated-on=\"" . date(get_option("date_format") . " " . get_option("time_format")) . "\""));

		switch($format) {
			case "sitemap":
				$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'));
				break;
			case "index":
				$this->AddElement(new GoogleSitemapGeneratorXmlEntry('<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'));
				break;
		}
	}

	/**
	 * Generates the footer for the sitemap with XML ending tag
	 *
	 * @since 4.0
	 * @param string $format The format, either sitemap for a sitemap or index for the sitemap index
	 */
	private function BuildSitemapFooter($format) {
		if(!in_array($format, array("sitemap", "index"))) $format = "sitemap";
		switch($format) {
			case "sitemap":
				$this->AddElement(new GoogleSitemapGeneratorXmlEntry('</urlset>'));
				break;
			case "index":
				$this->AddElement(new GoogleSitemapGeneratorXmlEntry('</sitemapindex>'));
				break;
		}
	}

	/**
	 * Adds information about time and memory usage to the sitemap
	 *
	 * @since 4.0
	 * @param float $startTime The microtime of the start
	 * @param int $startQueries
	 * @param int $startMemory
	 *
	 */
	private function AddEndCommend($startTime, $startQueries = 0, $startMemory = 0) {
		if(defined("WP_DEBUG") && WP_DEBUG) {
			echo "<!-- ";
			if(defined('SAVEQUERIES') && SAVEQUERIES) {
				echo '<pre>';
				var_dump($GLOBALS['wpdb']->queries);
				echo '</pre>';

				$total = 0;
				foreach($GLOBALS['wpdb']->queries as $q) {
					$total += $q[1];
				}
				echo '<h4>Total Query Time</h4>';
				echo '<pre>' . count($GLOBALS['wpdb']->queries) . ' queries in ' . round($total, 2) . ' seconds.</pre>';
			} else {
				echo '<p>Please edit wp-db.inc.php in wp-includes and set SAVEQUERIES to true if you want to see the queries.</p>';
			}
			echo " --> ";
		}
		$endTime = microtime(true);
		$endTime = round($endTime - $startTime, 2);
		$this->AddElement(new GoogleSitemapGeneratorDebugEntry("Request ID: " . md5(microtime()) . "; Queries for sitemap: " . ($GLOBALS["wpdb"]->num_queries - $startQueries) . "; Seconds: $endTime; Memory for sitemap: " . ((memory_get_peak_usage(true) - $startMemory) / 1024 / 1024) . "MB" . "; Total memory: " . (memory_get_peak_usage(true) / 1024 / 1024) . "MB"));
	}

	/**
	 * Adds the sitemap to the virtual robots.txt file
	 * This function is executed by WordPress with the do_robots hook
	 *
	 * @since 3.1.2
	 */
	public function DoRobots() {
		$this->Initate();
		if($this->GetOption('b_robots') === true) {

			$smUrl = $this->GetXmlUrl();

			echo "\nSitemap: " . $smUrl . "\n";
		}
	}


	/*************************************** SITEMAP CONTENT BUILDING ***************************************/

	/**
	 * Outputs an element in the sitemap
	 *
	 * @since 3.0
	 * @param $page GoogleSitemapGeneratorXmlEntry The element
	 */
	public function AddElement($page) {

		if(empty($page)) return;
		echo $page->Render();
	}

	/**
	 * Adds a url to the sitemap. You can use this method or call AddElement directly.
	 *
	 * @since 3.0
	 * @param $loc string The location (url) of the page
	 * @param $lastMod int The last Modification time as a UNIX timestamp
	 * @param $changeFreq string The change frequenty of the page, Valid values are "always", "hourly", "daily", "weekly", "monthly", "yearly" and "never".
	 * @param $priority float The priority of the page, between 0.0 and 1.0
	 * @param $postID int The post ID in case this is a post or page
	 * @see AddElement
	 * @return string The URL node
	 */
	public function AddUrl($loc, $lastMod = 0, $changeFreq = "monthly", $priority = 0.5, $postID = 0) {
		//Strip out the last modification time if activated
		if($this->GetOption('in_lastmod') === false) $lastMod = 0;
		$page = new GoogleSitemapGeneratorPage($loc, $priority, $changeFreq, $lastMod, $postID);

		do_action('sm_addurl', $page);

		if($this->simMode) {
			$caller = $this->GetExternalBacktrace(debug_backtrace());

			$this->simData["content"][] = array(
				"data" => $page,
				"caller" => $caller
			);
		} else {
			$this->AddElement($page);
		}
	}

	/**
	 * Add a sitemap entry to the index file
	 * @param $type
	 * @param string $params
	 * @param int $lastMod
	 */
	public function AddSitemap($type, $params = "", $lastMod = 0) {

		$url = $this->GetXmlUrl($type, $params);

		$sitemap = new GoogleSitemapGeneratorSitemapEntry($url, $lastMod);

		do_action('sm_addsitemap', $sitemap);

		if($this->simMode) {
			$caller = $this->GetExternalBacktrace(debug_backtrace());
			$this->simData["sitemaps"][] = array("data" => $sitemap, "type" => $type, "params" => $params, "caller" => $caller);
		} else {
			$this->AddElement($sitemap);
		}
	}


	/*************************************** PINGS ***************************************/

	/**
	 * Sends the pings to the search engines
	 */
	public function SendPing() {

		$this->LoadOptions();

		$status = new GoogleSitemapGeneratorStatus();

		$pingUrl = $this->GetXmlUrl();

		if($pingUrl) {
			$pings = array();

			if($this->GetOption("b_ping")) {
				$pings["google"] = array(
					"name" => "Google",
					"url" => "http://www.google.com/webmasters/sitemaps/ping?sitemap=%s",
					"check" => "successfully"
				);
			}

			if($this->GetOption("b_pingask")) {
				$pings["ask"] = array(
					"name" => "Ask.com",
					"url" => "http://submissions.ask.com/ping?sitemap=%s",
					"check" => "successfully received and added"
				);
			}

			if($this->GetOption("b_pingmsn")) {
				$pings["bing"] = array(
					"name" => "Bing",
					"url" => "http://www.bing.com/webmaster/ping.aspx?siteMap=%s",
					"check" => "感谢您提交站点地图。" // No way to check, response is IP-language-based :-(
				);
			}

			if($this->GetOption("b_pingyahoo") === true && $this->GetOption("b_yahookey")) {
				$pings["yahoo"] = array(
					"name" => "Yahoo",
					"url" => "http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=" . $this->GetOption("b_yahookey") . "&url=%s",
					"check" => "success"
				);
			}

			foreach($pings AS $serviceId => $service) {
				$url = str_replace("%s", urlencode($pingUrl), $service["url"]);
				$status->StartPing($serviceId, $url, $service["name"]);

				$pingres = $this->RemoteOpen($url);

				if($pingres === NULL || $pingres === false || strpos($pingres, $service["check"]) === false) {
					$status->EndPing($serviceId, false);
					trigger_error("Failed to ping $serviceId: " . htmlspecialchars(strip_tags($pingres)), E_USER_NOTICE);
				} else {
					$status->EndPing($serviceId, true);
				}
			}
		}

		$status->End();
	}

	/**
	 * Tries to ping a specific service showing as much as debug output as possible
	 * @since 3.1.9
	 * @return null
	 */
	public function ShowPingResult() {

		check_admin_referer('sitemap');

		if(!current_user_can("administrator")) {
			echo '<p>Please log in as admin</p>';
			return;
		}

		$service = !empty($_GET["sm_ping_service"]) ? $_GET["sm_ping_service"] : null;

		$status = GoogleSitemapGeneratorStatus::Load();

		if(!$status) die("No build status yet. Write something first.");

		$url = null;

		$services = $status->GetUsedPingServices();

		if(!in_array($service, $services)) die("Invalid service");

		$url = $status->GetPingUrl($service);

		if(empty($url)) die("Invalid ping url");

		echo '<html><head><title>Ping Test</title>';
		if(function_exists('wp_admin_css')) wp_admin_css('css/global', true);
		echo '</head><body><h1>Ping Test</h1>';

		echo '<p>Trying to ping: <a href="' . $url . '">' . $url . '</a>. The sections below should give you an idea whats going on.</p>';

		//Try to get as much as debug / error output as possible
		$errLevel = error_reporting(E_ALL);
		$errDisplay = ini_set("display_errors", 1);
		if(!defined('WP_DEBUG')) define('WP_DEBUG', true);

		echo '<h2>Errors, Warnings, Notices:</h2>';

		if(WP_DEBUG == false) echo "<i>WP_DEBUG was set to false somewhere before. You might not see all debug information until you remove this declaration!</i><br />";
		if(ini_get("display_errors") != 1) echo "<i>Your display_errors setting currently prevents the plugin from showing errors here. Please check your webserver logfile instead.</i><br />";

		$res = $this->RemoteOpen($url);

		echo '<h2>Result (text only):</h2>';

		echo wp_kses($res, array('a' => array('href' => array()), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array()));

		echo '<h2>Result (HTML):</h2>';

		echo htmlspecialchars($res);

		//Revert back old values
		error_reporting($errLevel);
		ini_set("display_errors", $errDisplay);
		echo '</body></html>';
		exit;
	}

	/**
	 * Opens a remote file using the WordPress API
	 * @since 3.0
	 * @param $url The URL to open
	 * @param $method string get or post
	 * @param $postData An array with key=>value paris
	 * @param $timeout int Timeout for the request, by default 10
	 * @return mixed False on error, the body of the response on success
	 */
	public static function RemoteOpen($url, $method = 'get', $postData = null, $timeout = 10) {
		global $wp_version;

		$options = array();
		$options['timeout'] = $timeout;

		if($method == 'get') {
			$response = wp_remote_get($url, $options);
		} else {
			$response = wp_remote_post($url, array_merge($options, array('body' => $postData)));
		}

		if(is_wp_error($response)) {
			$errs = $response->get_error_messages();
			$errs = htmlspecialchars(implode('; ', $errs));
			trigger_error('WP HTTP API Web Request failed: ' . $errs, E_USER_NOTICE);
			return false;
		}

		return $response['body'];
	}


	/*************************************** USER INTERFACE ***************************************/

	/**
	 * Includes the user interface class and intializes it
	 *
	 * @since 3.1.1
	 * @see GoogleSitemapGeneratorUI
	 * @return GoogleSitemapGeneratorUI
	 */
	private function GetUI() {

		global $wp_version;

		if($this->ui === null) {

			$className = 'GoogleSitemapGeneratorUI';
			$fileName = 'sitemap-ui.php';

			if(!class_exists($className)) {

				$path = trailingslashit(dirname(__FILE__));

				if(!file_exists($path . $fileName)) return false;
				require_once($path . $fileName);
			}

			$this->ui = new $className($this);

		}

		return $this->ui;
	}

	/**
	 * Shows the option page of the plugin. Before 3.1.1, this function was basically the UI, afterwards the UI was outsourced to another class
	 *
	 * @see GoogleSitemapGeneratorUI
	 * @since 3.0
	 * @return bool
	 */
	public function HtmlShowOptionsPage() {

		$ui = $this->GetUI();
		if($ui) {
			$ui->HtmlShowOptionsPage();
			return true;
		}

		return false;
	}
}
