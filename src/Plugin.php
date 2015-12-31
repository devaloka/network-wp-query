<?php
/**
 * Plugin
 *
 * @author Whizark <devaloka@whiark.com>
 * @see http://whizark.com
 * @license GPL-2.0
 * @license GPL-3.0
 */

namespace Devaloka\Plugin\NetworkWpQuery;

/**
 * Class Plugin
 *
 * @package Devaloka\Plugin\NetworkWpQuery
 */
class Plugin
{
    /**
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * The constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->msWpQuery  = new NetworkWpQuery($wpdb);
        $this->subscriber = new Subscriber($this->msWpQuery);
    }

    /**
     * Boots the plugin.
     */
    public function boot()
    {
        $this->subscriber->subscribe();
    }
}
