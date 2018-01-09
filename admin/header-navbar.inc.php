<?php
namespace CloudFrontPageCache;

/**
 * Admin header navbar template
 *
 * @package    cloudfront-page-cache
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

$active_view = (isset($_GET['view'])) ? $_GET['view'] : false;

// get budget stats
$budget_stats = $this->AdminInvalidation->budget_stats();

$budget_color = '';
if ($budget_stats['percentage'] >= 90) {
    $budget_color = 'color:red;font-weight:bold;';
} elseif ($budget_stats['percentage'] >= 80) {
    $budget_color = 'color:maroon;';
}

?>
<h2 class="nav-tab-wrapper wp-clearfix" style="padding-bottom:0px;">
	<a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache' ), admin_url('options-general.php'))); ?>" class="nav-tab">Welcome</a>
	<a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'settings' ), admin_url('options-general.php'))); ?>" class="nav-tab<?php print(($active_view === 'settings') ? ' nav-tab-active' : '');?>">Settings</a>
	<a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'setup' ), admin_url('options-general.php'))); ?>" class="nav-tab<?php print(($active_view === 'setup') ? ' nav-tab-active' : '');?>">Setup Guide</a>
    <a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'tests' ), admin_url('options-general.php'))); ?>" class="nav-tab<?php print(($active_view === 'tests') ? ' nav-tab-active' : '');?>">Speed Tests</a>
    <?php if ($this->options->bool('invalidation')) {
    ?>
    <a href="<?php print esc_url(add_query_arg(array( 'page' => 'cloudfront-page-cache', 'view' => 'invalidation' ), admin_url('options-general.php'))); ?>" class="nav-tab<?php print(($active_view === 'invalidation') ? ' nav-tab-active' : ''); ?>">Invalidation
    <span style="float:right;font-size:10px;line-height:12px;margin-left:10px;display:block;text-align:right;font-weight:normal;">
        Budget <?php print date('M \'y', current_time('timestamp')); ?><br /><span style="<?php print $budget_color; ?>"><?php print (string)$budget_stats['usage']; ?></span><?php if ($budget_stats['costs'] === 0) {
        print '/' . (string)$this->AdminInvalidation->budget();
    } else {
        print '/1k';
    }
    if ($budget_stats['costs']) {
        print ' (+$' . number_format_i18n($budget_stats['costs'], 2) . ')';
    } ?></span>
    </a>
	<?php
} ?>
</h2>