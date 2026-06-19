## 2024-05-14 - Selective Loading on Paginated Views
**Learning:** The `welcome()` method previously optimized queries by explicitly selecting columns to exclude the `longText` `content` field, but the same optimization was missed in the `index()` method, leading to unnecessary memory overhead when returning paginated lists of articles.
**Action:** Always check related list/index views when one query is optimized for column selection, as other queries returning the same model might also suffer from large unselected payload sizes (especially with `longText`/blobs).

## 2024-05-15 - Missing Cache on Single Model Fetches
**Learning:** In read-heavy scenarios where the main page fetches articles from cache, the individual read views (e.g., `show()`) might still be hitting the database directly (`Article::findOrFail($id)`).
**Action:** When working on read-heavy models (like articles or blog posts), always cache the single model fetch using `Cache::remember("key_{$id}", ...)` and ensure the background command/job responsible for syncing the data correctly invalidates the specific key using `$model->wasChanged()` to clear `Cache::forget("key_{$id}")`.

## 2026-06-09 - [Fixing 500 Error on Model Attributes]
**Learning:** In Laravel, model attributes retrieved from the database that represent dates (like `published_at`) need to be explicitly cast to `datetime` in the model definition to prevent errors when trying to use Carbon methods (like `diffForHumans()`) on them in views.
**Action:** Always ensure that timestamp or date columns are correctly cast in the model to avoid runtime errors in views.

## 2026-06-19 - Negative Caching on API Integration
**Learning:** In Laravel, `Cache::remember` evaluates the closure and only caches the return value. If an integration (like checking tracking numbers on an external API) throws an exception for an invalid request (e.g., "Not Found"), the exception bypasses the cache. This means bad requests repeatedly hit the external API, leading to potential rate-limit exhaustion.
**Action:** When caching volatile external API calls where invalid inputs are expected and throw exceptions, avoid `Cache::remember`. Manually use `Cache::get()` and explicitly `Cache::put()` both the successful data and the caught exceptions (negative caching) to protect external rate limits. Always version the cache key (`_v2`) when changing payload structure.
