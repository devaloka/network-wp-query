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
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var int
     */
    protected $siteId;

    /**
     * @var int[]
     */
    protected $siteIds = [];

    /**
     * @var string[]
     */
    protected $selectStatements;

    /**
     * @var bool
     */
    protected $isSwitched = false;

    /**
     * @var bool
     */
    protected $isInLoop = true;

    /**
     * The constructor.
     *
     * @param wpdb $wpdb
     */
    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * @param WP_Query $query
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
     * @param WP_Query $query
     */
    public function setUpLoop(WP_Query $query)
    {
        if (!$query->get('network')) {
            return;
        }

        $this->siteId     = get_current_blog_id();
        $this->isInLoop   = false;
        $this->isSwitched = false;
    }

    /**
     * @param WP_Post $post
     * @param WP_Query $query
     */
    public function setUpPost(WP_Post $post, WP_Query $query)
    {
        if (!$query->get('network')) {
            return;
        }

        if ($this->isInLoop || !isset($post->site_ID)) {
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
     * @param WP_Query $query
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
        $this->isInLoop   = true;
    }

    /**
     * @return string[]
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
     * @param string[] $clauses
     * @param WP_Query $query
     *
     * @return string[]
     */
    public function modifyClauses($clauses, WP_Query $query)
    {
        if (!$query->get('network')) {
            return $clauses;
        }

        $this->selectStatements = [];
        $rootSiteDbPrefix       = $this->wpdb->prefix;
        $postsPerSite           = (int) $query->get('posts_per_site', null);

        foreach ($this->siteIds as $siteId) {
            switch_to_blog($siteId);

            $selectStatement = $clauses['join'] . ' WHERE 1=1 ' . $clauses['where'];

            if ($clauses['groupby']) {
                $selectStatement .= ' GROUP BY ' . $clauses['groupby'];
            }

            if ($postsPerSite && $clauses['orderby']) {
                $selectStatement .= " ORDER BY {$clauses['orderby']} LIMIT 0, {$postsPerSite} ";
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
     * @param string $sql
     * @param WP_Query $query
     *
     * @return string
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
     * @param int[] $excludeIds
     * @param int[] $siteIds
     *
     * @return int[]
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
     * @param int[] $includeIds
     * @param int[] $siteIds
     *
     * @return int[]
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
     * @param int[] $siteIds
     * @param int[]|null $includeIds
     * @param int[]|null $excludeIds
     *
     * @return int[]
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
