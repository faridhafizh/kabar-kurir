<?php $__env->startSection('content'); ?>

<div class="hero animate-fade-in">
    <h1>Pantau Logistik<br><span style="color: var(--color-accent-primary)">Tanpa Sensor.</span></h1>
    <p>Agregator berita korporat dan platform pelacakan paket independen. Solusi nyata untuk memantau performa jaringan pengiriman di Indonesia.</p>

    <!-- Tracking Component using Alpine for State -->
    <div x-data="trackingApp()" class="card" style="max-width: 600px; margin: 0 auto; text-align: left;">
        <h3 class="mb-4">Lacak Pengiriman</h3>
        
        <!-- API Key Notice in dev if needed -->
        <div x-show="error" class="alert alert-danger" x-text="error" style="display:none;"></div>

        <form @submit.prevent="track" class="input-group">
            <span style="padding-left: 1rem; color: var(--color-text-muted);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input type="text" x-model="resi" class="input-field" placeholder="Masukkan Nomor Resi (mis: SPXID12345)" required maxlength="50" spellcheck="false" autocomplete="off">
            <button type="submit" class="btn btn-primary input-action" :disabled="loading">
                <span x-show="!loading">Lacak</span>
                <span x-show="loading">Memuat...</span>
            </button>
        </form>

        <!-- Tracking Results UI -->
        <template x-if="result">
            <div class="mt-8 animate-fade-in">
                <div class="flex justify-between items-center mb-4 pb-4" style="border-bottom: 1px solid var(--color-border)">
                    <div>
                        <div class="text-sm text-muted">Ekspedisi</div>
                        <div style="font-weight: 700; font-size: 1.25rem" x-text="result.summary.courier_name || 'Shopee Express'"></div>
                    </div>
                    <div class="badge badge-info" x-text="result.summary.status"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <div class="text-sm text-muted">Asal</div>
                        <div style="font-weight: 500;" x-text="result.summary.origin || '-'"></div>
                    </div>
                    <div>
                        <div class="text-sm text-muted">Tujuan</div>
                        <div style="font-weight: 500;" x-text="result.summary.destination || '-'"></div>
                    </div>
                </div>

                <h4 class="mb-4">Riwayat Pengiriman</h4>
                <div class="timeline">
                    <template x-for="(history, index) in result.history" :key="index">
                        <div class="timeline-item">
                            <div class="timeline-date" x-text="history.date"></div>
                            <div class="timeline-title" x-text="history.desc"></div>
                            <div class="timeline-desc" x-text="history.location"></div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<div class="mt-8">
    <div class="flex justify-between items-center mb-6">
        <h2>Sorotan Utama</h2>
        <a href="<?php echo e(route('news.index')); ?>" class="btn" style="border: 1px solid var(--color-border);">Lihat Semua &rarr;</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('news.show', $article->id)); ?>" class="card card-hover" style="display: flex; flex-direction: column; color: inherit;">
                <div class="text-xs text-muted mb-2 font-mono uppercase" style="letter-spacing: 0.05em; font-weight: 600;">
                    <?php echo e($article->source); ?> &middot; <?php echo e($article->published_at ? $article->published_at->diffForHumans() : 'Baru saja'); ?>

                </div>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem;"><?php echo e($article->title); ?></h3>
                <p class="text-sm text-muted" style="flex: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php echo e($article->description); ?>

                </p>
                <div class="mt-4 text-sm" style="color: var(--color-accent-primary); font-weight: 500;">Baca selengkapnya &rarr;</div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card md:col-span-3 text-center text-muted" style="padding: 3rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1rem; opacity: 0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Belum ada berita yang ditarik dari API. Jalankan `php artisan news:fetch` jika NewsAPI sudah dikonfigurasi.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function trackingApp() {
    return {
        resi: '',
        loading: false,
        error: '',
        result: null,
        async track() {
            if (!this.resi) return;
            this.loading = true;
            this.error = '';
            this.result = null;

            try {
                const response = await fetch(`/api/track?resi=${encodeURIComponent(this.resi)}`);
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal melacak resi.');
                }
                
                this.result = data;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Websites\spx-news\resources\views/welcome.blade.php ENDPATH**/ ?>