# Change Log

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

<a name="0.2.0"></a>
# [0.2.0](https://github.com/devaloka/network-wp-query/compare/v0.1.3...v0.2.0) (2017-04-09)


### Features

* include all network-wide posts by default ([ce27642](https://github.com/devaloka/network-wp-query/commit/ce27642))


### BREAKING CHANGES

* The default value of `posts_per_site` becomes the same value of
`posts_per_page`.



<a name="0.1.3"></a>
## [0.1.3](https://github.com/devaloka/network-wp-query/compare/v0.1.2...v0.1.3) (2016-04-02)


### Bug Fixes

* Domain Path in MU plugin loader ([140c486](https://github.com/devaloka/network-wp-query/commit/140c486))



<a name="0.1.2"></a>
## [0.1.2](https://github.com/devaloka/network-wp-query/compare/v0.1.1...v0.1.2) (2016-01-08)


### Bug Fixes

* cast WP_Post::site_ID to integer ([4b3d74e](https://github.com/devaloka/network-wp-query/commit/4b3d74e))
* in-loop state false positive ([ba8a94d](https://github.com/devaloka/network-wp-query/commit/ba8a94d)), closes [#4](https://github.com/devaloka/network-wp-query/issues/4)

### Features

* add `posts_per_site` filter hook ([20dbec6](https://github.com/devaloka/network-wp-query/commit/20dbec6)), closes [#6](https://github.com/devaloka/network-wp-query/issues/6)
* add get_the_site_ID(), the_site_ID() as template functions ([5b6c066](https://github.com/devaloka/network-wp-query/commit/5b6c066)), closes [#1](https://github.com/devaloka/network-wp-query/issues/1)



<a name="0.1.1"></a>
## [0.1.1](https://github.com/devaloka/network-wp-query/compare/v0.1.0...v0.1.1) (2015-12-31)


### Bug Fixes

* incorrect isset() check for WP_Post::site_ID ([eea8869](https://github.com/devaloka/network-wp-query/commit/eea8869))
* missing property declaration ([f6583fa](https://github.com/devaloka/network-wp-query/commit/f6583fa))



<a name="0.1.0"></a>
# 0.1.0 (2015-12-31)

* The first release
