<?php $__env->startSection('content'); ?>
<div class="mb-8" style="padding-top: 2rem;">
    <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Sorotan Berita Ekspedisi</h1>
    <p class="text-muted" style="margin-bottom: 2rem;">Pantau terus berita terkini seputar layanan logistik, keluhan, dan regulasi di bawah radar pengawasan.</p>

    <!-- Search Form -->
    <form action="<?php echo e(route('news.index')); ?>" method="GET" class="input-group" style="max-width: 500px; margin-bottom: 3rem;">
        <span style="padding-left: 1rem; color: var(--color-text-muted);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </span>
        <input type="text" name="search" class="input-field" placeholder="Cari berdasarkan judul, sumber..." value="<?php echo e(request('search')); ?>">
        <button type="submit" class="btn btn-primary input-action" style="margin: 0.5rem; padding: 0.5rem 1.25rem;">Cari</button>
    </form>

    <!-- Articles List -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('news.show', $article->id)); ?>" class="card card-hover" style="display: flex; flex-direction: column; gap: 0.5rem; color: inherit; text-decoration: none;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="text-xs font-mono uppercase" style="color: var(--color-accent-primary); letter-spacing: 0.05em; font-weight: 700;"><?php echo e($article->source); ?></span>
                    <span class="text-xs text-muted"><?php echo e($article->published_at ? $article->published_at->translatedFormat('d M Y, H:i') : '-'); ?></span>
                </div>
                <h2 style="font-size: 1.5rem; line-height: 1.3;"><?php echo e($article->title); ?></h2>
                <p class="text-muted" style="line-height: 1.6;"><?php echo e($article->description); ?></p>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card text-center text-muted" style="padding: 4rem 1rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1rem; opacity: 0.5;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <p>Tidak ada berita yang ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if($articles->hasPages()): ?>
        <div class="pagination">
            <?php echo e($articles->withQueryString()->links('pagination::bootstrap-4')); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Websites\spx-news\resources\views/news/index.blade.php ENDPATH**/ ?>