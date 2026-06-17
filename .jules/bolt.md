## 2024-05-14 - Selective Loading on Paginated Views
**Learning:** The `welcome()` method previously optimized queries by explicitly selecting columns to exclude the `longText` `content` field, but the same optimization was missed in the `index()` method, leading to unnecessary memory overhead when returning paginated lists of articles.
**Action:** Always check related list/index views when one query is optimized for column selection, as other queries returning the same model might also suffer from large unselected payload sizes (especially with `longText`/blobs).

## 2024-05-15 - Missing Cache on Single Model Fetches
**Learning:** In read-heavy scenarios where the main page fetches articles from cache, the individual read views (e.g., `show()`) might still be hitting the database directly (`Article::findOrFail($id)`).
**Action:** When working on read-heavy models (like articles or blog posts), always cache the single model fetch using `Cache::remember("key_{$id}", ...)` and ensure the background command/job responsible for syncing the data correctly invalidates the specific key using `$model->wasChanged()` to clear `Cache::forget("key_{$id}")`.

## 2026-06-09 - [Fixing 500 Error on Model Attributes]
**Learning:** In Laravel, model attributes retrieved from the database that represent dates (like `published_at`) need to be explicitly cast to `datetime` in the model definition to prevent errors when trying to use Carbon methods (like `diffForHumans()`) on them in views.
**Action:** Always ensure that timestamp or date columns are correctly cast in the model to avoid runtime errors in views.

## 2024-06-17 - Negative Caching on External APIs
**Learning:** In the `TrackingController`, invalid queries (like checking a non-existent tracking number) or errors hitting the external BinderByte API previously threw exceptions. Because `Cache::remember()` only caches successful returns from the closure and skips caching if an exception is thrown, malicious users or repeated invalid inputs could continuously hammer the external API, rapidly exhausting rate limits.
**Action:** When caching external API calls or operations with rate limits, always implement "negative caching". Ditch `Cache::remember()` for a manual `Cache::get()` and `Cache::put()` pattern. Catch exceptions or error states, explicitly cache those error states for a short duration, and serve them to prevent repeated outgoing invalid requests.
