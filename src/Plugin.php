<?php
/**
 * Plugin
 *
 * @author Whizark <devaloka@whiark.com>
 * @see http://whizark.com
 * @copyright Copyright (C) 2015 Whizark.
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
     * @var NetworkWpQuery
     */
    protected $networkWpQuery;

    /**
     * The constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->networkWpQuery = new NetworkWpQuery($wpdb);
        $this->subscriber     = new Subscriber($this->networkWpQuery);
    }

    /**
     * Boots the plugin.
     */
    public function boot()
    {
        $this->subscriber->subscribe();
    }
}
