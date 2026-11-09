<?php $pages=max(1,(int)ceil($total/$p['per_page']));$page=min($p['page'],$pages); ?>
<div class="table-bottom">
  <span>Menampilkan <strong><?= $total && $p['offset']<$total ? number_format($p['offset']+1,0,',','.') : 0 ?>–<?= number_format(min($p['offset']+$p['per_page'],$total),0,',','.') ?></strong> dari <strong><?= number_format($total,0,',','.') ?></strong> data</span>
  <div class="pager">
    <a class="pager-button <?= $page<=1?'is-disabled':'' ?>" href="<?= h(qs_link($path,array('page'=>max(1,$page-1)))) ?>" aria-label="Halaman sebelumnya">←</a>
    <span>Halaman <?= $page ?> / <?= $pages ?></span>
    <a class="pager-button <?= $page>=$pages?'is-disabled':'' ?>" href="<?= h(qs_link($path,array('page'=>min($pages,$page+1)))) ?>" aria-label="Halaman berikutnya">→</a>
  </div>
</div>
