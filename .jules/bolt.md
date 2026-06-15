## 2024-05-14 - Selective Loading on Paginated Views
**Learning:** The `welcome()` method previously optimized queries by explicitly selecting columns to exclude the `longText` `content` field, but the same optimization was missed in the `index()` method, leading to unnecessary memory overhead when returning paginated lists of articles.
**Action:** Always check related list/index views when one query is optimized for column selection, as other queries returning the same model might also suffer from large unselected payload sizes (especially with `longText`/blobs).

## 2024-05-15 - Missing Cache on Single Model Fetches
**Learning:** In read-heavy scenarios where the main page fetches articles from cache, the individual read views (e.g., `show()`) might still be hitting the database directly (`Article::findOrFail($id)`).
**Action:** When working on read-heavy models (like articles or blog posts), always cache the single model fetch using `Cache::remember("key_{$id}", ...)` and ensure the background command/job responsible for syncing the data correctly invalidates the specific key using `$model->wasChanged()` to clear `Cache::forget("key_{$id}")`.

## 2026-06-09 - [Fixing 500 Error on Model Attributes]
**Learning:** In Laravel, model attributes retrieved from the database that represent dates (like `published_at`) need to be explicitly cast to `datetime` in the model definition to prevent errors when trying to use Carbon methods (like `diffForHumans()`) on them in views.
**Action:** Always ensure that timestamp or date columns are correctly cast in the model to avoid runtime errors in views.

## 2024-05-16 - Negative Caching and Deployments
**Learning:** In Laravel, `Cache::remember` evaluates exceptions thrown within the closure as failures and skips caching. If an API returns logic errors (like 'not found') using exceptions, you need to use manual `Cache::get` and `Cache::put` to cache these expected errors (negative caching) to avoid rate limiting. Furthermore, when changing the *structure* of cached data (e.g., wrapping it in an array to differentiate errors), the cache key *must* be versioned to avoid breaking errors on deploy for users with legacy cache entries.
**Action:** When implementing explicit caching to handle errors (negative caching), always version the cache key if the structure of the data being stored differs from previous versions.
