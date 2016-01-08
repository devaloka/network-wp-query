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
     * @var NetworkWpQuery
     */
    protected $networkWpQuery;

    /**
     * The constructor.
     *
     * @param NetworkWpQuery $networkWpQuery
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
     * @param mixed[] $queryVars
     *
     * @return mixed[]
     */
    public function onQueryVars(array $queryVars)
    {
        $queryVars = array_merge($queryVars, $this->networkWpQuery->getQueryVars());

        return array_unique($queryVars);
    }

    /**
     * @param WP_Query $query
     */
    public function onPreGetPosts(WP_Query $query)
    {
        $this->networkWpQuery->setUpQuery($query);
    }

    /**
     * @param string[] $clauses
     * @param WP_Query $query
     *
     * @return string
     */
    public function onPostsClauses($clauses, WP_Query $query)
    {
        return $this->networkWpQuery->modifyClauses($clauses, $query);
    }

    /**
     * @param string $sql
     * @param WP_Query $query
     *
     * @return string
     */
    public function onPostsRequest($sql, WP_Query $query)
    {
        return $this->networkWpQuery->modifyQuery($sql, $query);
    }

    /**
     * @param WP_Post[] $posts
     * @param WP_Query $query
     *
     * @return WP_Post[]
     */
    public function onThePosts(array $posts, WP_Query $query)
    {
        return $this->networkWpQuery->modifyPosts($posts, $query);
    }

    /**
     * @param WP_Query $query
     */
    public function onLoopStart(WP_Query $query)
    {
        $this->networkWpQuery->setUpLoop($query);
    }

    /**
     * @param WP_Post $post
     * @param WP_Query $query
     */
    public function onThePost(WP_Post $post, WP_Query $query)
    {
        $this->networkWpQuery->setUpPost($post, $query);
    }

    /**
     * @param WP_Query $query
     */
    public function onLoopEnd(WP_Query $query)
    {
        $this->networkWpQuery->tearDownLoop($query);
    }
}
