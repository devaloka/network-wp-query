<?php
/**
 * Subscriber
 *
 * @author Whizark <devaloka@whiark.com>
 * @see http://whizark.com
 * @copyright Copyright (C) 2015 Whizark.
 * @license GPL-2.0
 * @license GPL-3.0
 */

namespace Devaloka\Plugin\NetworkWpQuery;

use WP_Post;
use WP_Query;

/**
 * Class Subscriber
 *
 * @package Devaloka\Plugin\NetworkWpQuery
 */
class Subscriber
{
    /**
     * @var NetworkWpQuery An instance of NetworkWpQuery.
     */
    protected $networkWpQuery;

    /**
     * The constructor.
     *
     * @param NetworkWpQuery $networkWpQuery An instance of NetworkWpQuery.
     */
    public function __construct($networkWpQuery)
    {
        $this->networkWpQuery = $networkWpQuery;
    }

    /**
     * Subscribes actions & filters.
     */
    public function subscribe()
    {
        add_filter('query_vars', [$this, 'onQueryVars']);
        add_action('pre_get_posts', [$this, 'onPreGetPosts'], PHP_INT_MAX);
        add_filter('posts_clauses', [$this, 'onPostsClauses'], 10, 2);
        add_filter('posts_request', [$this, 'onPostsRequest'], 10, 2);
        add_action('the_posts', [$this, 'onThePosts'], 10, 2);
        add_action('loop_start', [$this, 'onLoopStart']);
        add_action('the_post', [$this, 'onThePost'], 10, 2);
        add_action('loop_end', [$this, 'onLoopEnd']);
    }

    /**
     * The `query_vars` filter listener.
     *
     * Returns the query variables whitelist.
     *
     * @param mixed[] $queryVars The query vars.
     *
     * @return mixed[] The query vars.
     */
    public function onQueryVars(array $queryVars)
    {
        $queryVars = array_merge($queryVars, $this->networkWpQuery->getQueryVars());

        return array_unique($queryVars);
    }

    /**
     * The `pre_get_posts` action listener.
     *
     * @param WP_Query $query The WP_Query instance.
     */
    public function onPreGetPosts(WP_Query $query)
    {
        $this->networkWpQuery->setUpQuery($query);
    }

    /**
     * The `posts_clauses` filter listener.
     *
     * @param string[] $clauses The array of clauses for the query.
     * @param WP_Query $query The WP_Query instance.
     *
     * @return string[] The array of clauses for the query.
     */
    public function onPostsClauses($clauses, WP_Query $query)
    {
        return $this->networkWpQuery->modifyClauses($clauses, $query);
    }

    /**
     * The `posts_request` filter listener.
     *
     * @param string $sql The complete SQL query.
     * @param WP_Query $query The WP_Query instance.
     *
     * @return string The complete SQL query.
     */
    public function onPostsRequest($sql, WP_Query $query)
    {
        return $this->networkWpQuery->modifyQuery($sql, $query);
    }

    /**
     * The `the_posts` action listener.
     *
     * @param WP_Post[] $posts The array of retrieved posts.
     * @param WP_Query $query The WP_Query instance.
     *
     * @return WP_Post[] The array of posts.
     */
    public function onThePosts(array $posts, WP_Query $query)
    {
        return $this->networkWpQuery->modifyPosts($posts, $query);
    }

    /**
     * The `loop_start` action listener.
     *
     * @param WP_Query $query The WP_Query instance.
     */
    public function onLoopStart(WP_Query $query)
    {
        $this->networkWpQuery->setUpLoop($query);
    }

    /**
     * The `the_post` action listener.
     *
     * @param WP_Post $post The WP_Post instance.
     * @param WP_Query $query The WP_Query instance.
     */
    public function onThePost(WP_Post $post, WP_Query $query)
    {
        $this->networkWpQuery->setUpPost($post, $query);
    }

    /**
     * The `loop_end` action listener.
     *
     * @param WP_Query $query The WP_Query instance.
     */
    public function onLoopEnd(WP_Query $query)
    {
        $this->networkWpQuery->tearDownLoop($query);
    }
}
