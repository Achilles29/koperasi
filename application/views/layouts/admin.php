<!doctype html>
<html lang="id">
<?php $site=site_profile(); ?>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($title) ?> · <?= h($site['site_name']) ?></title>
  <link rel="icon" href="<?= base_url($site['favicon_path']) ?>"><link rel="stylesheet" href="<?= base_url('assets/vendor/tabler/tabler.min.css') ?>"><link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=20261105j') ?>">
</head>
<body class="admin-body">
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <a class="sidebar-brand" href="<?= site_url('admin') ?>"><img class="pemkab-logo" width="35" height="47" style="width:35px;height:47px;object-fit:contain;flex:none" src="<?= base_url($site['logo_path']) ?>" alt="<?= h($site['government_name']) ?>"><span><strong><?= h($site['site_name']) ?></strong><small><?= h($site['division_name']) ?></small></span></a>
    <div class="sidebar-caption"><?= h($site['division_name']) ?></div>
    <nav class="side-nav" aria-label="Navigasi utama">
      <?php if ($allowed_nav['dashboard']): ?><a class="nav-item <?= $active_nav==='dashboard'?'is-active':'' ?>" href="<?= site_url('admin') ?>"><span class="nav-glyph">01</span> Ringkasan</a><?php endif; ?>
      <?php foreach (array(
        'DATA & PETA'=>array('koperasi'=>'Data koperasi','kdkmp'=>'KDKMP','gis'=>'Peta koperasi','wilayah'=>'Master wilayah','master'=>'Master data'),
        'PENILAIAN & PENGAWASAN'=>array('penilaian'=>'Penilaian kesehatan','pengawasan'=>'Pengawasan','laporan_tahunan'=>'Laporan tahunan','laporan'=>'Laporan bidang'),
        'LAYANAN & PEMBINAAN'=>array('layanan'=>'Layanan izin','aduan'=>'Aduan','pembinaan'=>'Pembinaan & RAT'),
        'PUBLIKASI'=>array('konten'=>'Konten publik'),
        'PENGATURAN'=>array('profil_dinas'=>'Profil dinas','pengguna'=>'Pengguna & akses')
      ) as $group=>$items): ?>
        <?php if (array_sum(array_intersect_key($allowed_nav,array_flip(array_keys($items))))): ?><div class="nav-label"><?= h($group) ?></div><?php endif; ?>
        <?php foreach ($items as $slug=>$label): if (!$allowed_nav[$slug])continue;
          $href=$slug==='koperasi'?'admin/koperasi':($slug==='wilayah'?'admin/wilayah':($slug==='master'?'admin/master':($slug==='profil_dinas'?'admin/profil-dinas':($slug==='pengguna'?'admin/pengguna':($slug==='laporan_tahunan'?'admin/modul/laporan-tahunan':'admin/modul/'.$slug))))); ?>
          <a class="nav-item <?= $active_nav===$slug?'is-active':'' ?>" href="<?= site_url($href) ?>"><span class="nav-tick"></span><?= h($label) ?></a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-foot"><span class="status-dot"></span> Basis data aktif <small>Data awal ODS · 4 Nov 2026</small></div>
  </aside>
  <div class="main-area">
    <header class="topbar">
      <button class="menu-toggle" type="button" aria-label="Buka menu" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
      <div class="topbar-trail"><?= h($site['opd_name']) ?> <span>/</span> <?= h($site['government_name']) ?></div>
      <div class="topbar-actions"><span class="user-badge"><?= h(mb_substr($user['full_name'],0,1)) ?></span><span class="user-name"><?= h($user['full_name']) ?></span><a class="logout-link" href="<?= site_url('logout') ?>">Keluar</a></div>
    </header>
    <main class="page-content">
      <div class="page-heading"><div><div class="eyebrow"><?= h($eyebrow) ?></div><h1><?= h($title) ?></h1></div><div class="heading-rule"></div></div>
      <?php if ($this->session->flashdata('success')): ?><div class="notice notice-success"><?= h($this->session->flashdata('success')) ?></div><?php endif; ?>
      <?php if ($this->session->flashdata('error')): ?><div class="notice notice-error"><?= h($this->session->flashdata('error')) ?></div><?php endif; ?>
      <?php $this->load->view($content_view); ?>
    </main>
    <footer class="app-footer"><?= h($site['site_name']) ?> <span>·</span> <?= h($site['division_name']) ?> <span>·</span> <?= date('Y') ?></footer>
  </div>
</div>
<script src="<?= base_url('assets/vendor/tabler/tabler.min.js') ?>"></script></body>
</html>
