<?php $site=site_profile(); if ($site['address'] || $site['phone'] || $site['email'] || $site['website_url'] || $site['instagram_url'] || $site['facebook_url'] || $site['youtube_url']): ?>
<div class="public-contact-links">
<?php if ($site['address']): ?><span><?= h($site['address']) ?></span><?php endif; ?>
<?php if ($site['phone']): ?><span><?= h($site['phone']) ?></span><?php endif; ?>
<?php if ($site['email']): ?><a href="mailto:<?= h($site['email']) ?>"><?= h($site['email']) ?></a><?php endif; ?>
<?php foreach (array('website_url'=>'Situs resmi','instagram_url'=>'Instagram','facebook_url'=>'Facebook','youtube_url'=>'YouTube') as $key=>$label): if (!$site[$key])continue; ?><a href="<?= h($site[$key]) ?>" target="_blank" rel="noopener noreferrer"><?= h($label) ?> ↗</a><?php endforeach; ?>
</div><?php endif; ?>
