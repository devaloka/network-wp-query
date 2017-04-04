<?php
/**
 * Network WP_Query
 *
 * @author Eric Lewis <eric.andrew.lewis@gmail.com>
 * @see http://www.ericandrewlewis.com
 * @see https://github.com/ericandrewlewis/WP_Query_Multisite
 *
 * @author Miguel Peixe <miguel@cardume.art.br>
 * @see http://codigourbano.org
 * @see https://github.com/miguelpeixe/WP_Query_Multisite
 *
 * @author Whizark <devaloka@whiark.com>
 * @see http://whizark.com
 *
 * @license GPL-2.0
 * @license GPL-3.0
 */

namespace Devaloka\Plugin\NetworkWpQuery;

use WP_Post;
use WP_Query;
use wpdb;

/**
 * Class NetworkWpQuery
 *
 * @package Devaloka\Plugin\NetworkWpQuery
 */
class NetworkWpQuery
{
    /**
     * @var wpdb An instance of wpdb.
     */
    protected $wpdb;

    /**
     * @var int[] The Site IDs to query.
     */
    protected $siteIds = [];

    /**
     * @var string[] The array of SELECT statements for Sites to query.
     */
    protected $selectStatements;

    /**
     * @var bool Whether the current Site is switched in a loop.
     */
    protected $isSwitched = false;

    /**
     * The constructor.
     *
     * @param wpdb $wpdb An instance of wpdb.
     */
    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * Sets up for a query.
     *
     * @param WP_Query $query An instance of WP_Query.
     */
    public function setUpQuery(WP_Query $query)
    {
        if (!$query->get('network')) {
            return;
        }

        $siteIds = $this->wpdb->get_col("SELECT blog_id FROM {$this->wpdb->blogs}");
        $siteIds = array_map(
            function ($siteId) {
                return (int) $siteId;
            },
            $siteIds
        );

        $includeIds = $query->get('sites__in', []);
        $includeIds = array_map(
            function ($includeId) {
                return (int) $includeId;
            },
            $includeIds
        );

        $excludeIds = $query->get('sites__not_in', []);
        $excludeIds = array_map(
            function ($excludeId) {
                return (int) $excludeId;
            },
            $excludeIds
        );

        $this->siteIds = $this->filterSiteIds($siteIds, $includeIds, $excludeIds);
    }

    /**
     * Modifies WP_Post objects.
     *
     * Casts WP_Post::site_ID to integer.
     *
     * @param WP_Post[] $posts A array of WP_Post.
     *
     * @return WP_Post[] The modified array of WP_Post.
     */
    public function modifyPosts(array $posts, WP_Query $query)
    {
        if (!$query->get('network')) {
            return $posts;
        }

        foreach ($posts as $post) {
            if (!isset($post->site_ID)) {
                continue;
            }

            $post->site_ID = (int) $post->site_ID;
        }

        return $posts;
    }

    /**
     * Sets up for a loop.
     *
     * @param WP_Query $query An instance of WP_Query.
     */
    public function setUpLoop(WP_Query $query)
    {
        if (!$query->get('network')) {
            return;
        }

        $this->isSwitched = false;
    }

    /**
     * Sets up for a Post in a loop.
     *
     * Restores/Switches the current Site.
     *
     * @param WP_Post $post An instance of WP_Post.
     * @param WP_Query $query An instance of WP_Query.
     */
    public function setUpPost(WP_Post $post, WP_Query $query)
    {
        if (!$query->get('network')) {
            return;
        }

        if (!$query->in_the_loop || !isset($post->site_ID)) {
            return;
        }

        $blogId = get_current_blog_id();

        if ($blogId === $post->site_ID) {
            return;
        }

        if ($this->isSwitched) {
            restore_current_blog();
        }

        switch_to_blog($post->site_ID);

        $this->isSwitched = true;
    }

    /**
     * Tears down for a loop.
     *
     * Restores the current Site.
     *
     * @param WP_Query $query An instance of WP_Query.
     */
    public function tearDownLoop(WP_Query $query)
    {
        if (!$query->get('network')) {
            return;
        }

        if ($this->isSwitched) {
            restore_current_blog();
        }

        $this->isSwitched = false;
    }

    /**
     * Gets query variables whitelist.
     *
     * @return string[] The query variables whitelist.
     */
    public function getQueryVars()
    {
        return [
            'network',
            'sites__not_in',
            'sites__in',
            'posts_per_site',
        ];
    }

    /**
     * Modifies query clauses.
     *
     * @param string[] $clauses The array of clauses for a query.
     * @param WP_Query $query An instance of WP_Query.
     *
     * @return string[] The modified clauses.
     */
    public function modifyClauses($clauses, WP_Query $query)
    {
        if (!$query->get('network')) {
            return $clauses;
        }

        $this->selectStatements = [];
        $rootSiteDbPrefix       = $this->wpdb->prefix;
        $postsPerPage           = (int) $query->get('posts_per_page', get_option('posts_per_page'));
        $postsPerSite           = (int) $query->get('posts_per_site', $postsPerPage);

        foreach ($this->siteIds as $siteId) {
            switch_to_blog($siteId);

            $postsPerSiteForTheSite = apply_filters('posts_per_site', $postsPerSite, $siteId, $query);

            if (!$postsPerSiteForTheSite) {
                restore_current_blog();

                continue;
            }

            $selectStatement = $clauses['join'] . ' WHERE 1=1 ' . $clauses['where'];

            if ($clauses['groupby']) {
                $selectStatement .= ' GROUP BY ' . $clauses['groupby'];
            }

            if ($clauses['orderby']) {
                $selectStatement .= " ORDER BY {$clauses['orderby']} LIMIT 0, {$postsPerSiteForTheSite} ";
            }

            $selectStatement = str_replace($rootSiteDbPrefix, $this->wpdb->prefix, $selectStatement);
            $selectStatement = " ( SELECT {$this->wpdb->posts}.*, '$siteId' AS site_ID " .
                "FROM {$this->wpdb->posts} {$selectStatement} ) ";

            $this->selectStatements[] = $selectStatement;

            restore_current_blog();
        }

        $clauses['join']    = '';
        $clauses['where']   = '';
        $clauses['groupby'] = '';

        $clauses['orderby'] = str_replace($this->wpdb->posts, 'tables', $clauses['orderby']);

        return $clauses;
    }

    /**
     * Modifies a SQL query.
     *
     * @param string $sql The complete SQL query.
     * @param WP_Query $query An instance of WP_Query.
     *
     * @return string The modified SQL query.
     */
    public function modifyQuery($sql, WP_Query $query)
    {
        if (!$query->get('network')) {
            return $sql;
        }

        $sql = preg_replace('/WHERE\s+1=1/i', '', $sql);
        $sql = preg_replace(
            "/{$this->wpdb->posts}.*\s+FROM\s+{$this->wpdb->posts}/i",
            'tables.* FROM ( ' . implode(' UNION ', $this->selectStatements) . ' ) tables',
            $sql
        );

        return $sql;
    }

    /**
     * Removes some Site IDs from an array of Sites IDs.
     *
     * @param int[] $excludeIds The site IDs to exclude.
     * @param int[] $siteIds An array of Site IDs.
     *
     * @return int[] The filtered array of Site IDs.
     */
    protected function excludeSiteIds(array $excludeIds, array $siteIds)
    {
        return array_filter(
            $siteIds,
            function ($siteId) use ($excludeIds) {
                return !in_array($siteId, $excludeIds, true);
            }
        );
    }

    /**
     * Picks up some Site Ids from an array of Sites IDs.
     *
     * @param int[] $includeIds The site IDs to include.
     * @param int[] $siteIds An array of Site Ids.
     *
     * @return int[] The filtered array of Site IDs.
     */
    protected function includeSiteIds(array $includeIds, array $siteIds)
    {
        return array_filter(
            $siteIds,
            function ($siteId) use ($includeIds) {
                return in_array($siteId, $includeIds, true);
            }
        );
    }

    /**
     * Filters Site IDs.
     *
     * Picks up/Removes some Site IDs from an array of Site IDs.
     *
     * @param int[] $siteIds The Site ID to filter.
     * @param int[]|null $includeIds An array of Site IDs to include.
     * @param int[]|null $excludeIds An array of Site IDs to exclude.
     *
     * @return int[] The filtered array of Site IDs.
     */
    protected function filterSiteIds(array $siteIds, array $includeIds = [], array $excludeIds = [])
    {
        if (count($includeIds) >= 1) {
            $siteIds = $this->includeSiteIds($includeIds, $siteIds);
        }

        if (count($excludeIds) >= 1) {
            $siteIds = $this->excludeSiteIds($excludeIds, $siteIds);
        }

        return array_values($siteIds);
    }
}
