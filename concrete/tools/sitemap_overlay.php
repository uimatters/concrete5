<?php
defined('C5_EXECUTE') or die("Access Denied.");

$sh = Loader::helper('concrete/dashboard/sitemap');
if (!$sh->canRead()) {
    die(t('Access Denied'));
}

$v = View::getInstance();
$v->requireAsset('core/sitemap');

/*
$txt = Loader::helper('text');
$args = $_REQUEST;
foreach($args as $key => $value) {
    if (is_array($value)) {
        foreach ($value as $index => $id) {
            $value[$index] = intval($id);
        }
    } else {
        $args[$key] = $txt->entities($value);
    }
}

if (isset($select_mode)) {
    $args['select_mode'] = $select_mode;
}
$args['selectedPageID'] = $_REQUEST['cID'];
if (is_array($args['selectedPageID'])) {
    $args['selectedPageID'] = implode(',',$args['selectedPageID']);
}
$args['sitemapCombinedMode'] = $sitemapCombinedMode;
if (!isset($args['select_mode'])) {
    $args['select_mode'] = 'select_page';
}
if ($args['select_mode'] == 'select_page') {
    $args['reveal'] = $args['selectedPageID'];
}

$args['display_mode'] = 'full';
$args['instance_id'] = time();
*/
?>

<div class="ccm-sitemap-overlay"></div>


<script type="text/javascript">
    $(function () {
        $('.ccm-sitemap-overlay').concreteSitemap({
            onClickNode: function (node) {
                ConcreteEvent.publish('SitemapSelectPage', {
                    cID: node.data.cID,
                    title: node.data.title,
                    instance: this
                });
            },
            displaySingleLevel: <?= $_REQUEST['display'] == 'flat' ? 'true' : 'false' ?>,
        });
    });
</script>
