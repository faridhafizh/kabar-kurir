## 2024-05-14 - Selective Loading on Paginated Views
**Learning:** The `welcome()` method previously optimized queries by explicitly selecting columns to exclude the `longText` `content` field, but the same optimization was missed in the `index()` method, leading to unnecessary memory overhead when returning paginated lists of articles.
**Action:** Always check related list/index views when one query is optimized for column selection, as other queries returning the same model might also suffer from large unselected payload sizes (especially with `longText`/blobs).

## 2024-05-15 - Missing Cache on Single Model Fetches
**Learning:** In read-heavy scenarios where the main page fetches articles from cache, the individual read views (e.g., `show()`) might still be hitting the database directly (`Article::findOrFail($id)`).
**Action:** When working on read-heavy models (like articles or blog posts), always cache the single model fetch using `Cache::remember("key_{$id}", ...)` and ensure the background command/job responsible for syncing the data correctly invalidates the specific key using `$model->wasChanged()` to clear `Cache::forget("key_{$id}")`.

## 2026-06-09 - [Fixing 500 Error on Model Attributes]
**Learning:** In Laravel, model attributes retrieved from the database that represent dates (like `published_at`) need to be explicitly cast to `datetime` in the model definition to prevent errors when trying to use Carbon methods (like `diffForHumans()`) on them in views.
**Action:** Always ensure that timestamp or date columns are correctly cast in the model to avoid runtime errors in views.

## 2026-06-14 - Negative Caching on External APIs
**Learning:** When making external API calls that throw exceptions on expected errors (like 'not found' trackings), using `Cache::remember` will bypass the cache completely if the closure throws an exception. This can lead to exhausting external API rate limits with repeated invalid requests.
**Action:** Instead of `Cache::remember`, manually use `Cache::get()` and `Cache::put()` to explicitly cache both successful responses AND error states (negative caching) to prevent repeated bad requests from hitting the external server.
