## 2024-05-14 - Selective Loading on Paginated Views
**Learning:** The `welcome()` method previously optimized queries by explicitly selecting columns to exclude the `longText` `content` field, but the same optimization was missed in the `index()` method, leading to unnecessary memory overhead when returning paginated lists of articles.
**Action:** Always check related list/index views when one query is optimized for column selection, as other queries returning the same model might also suffer from large unselected payload sizes (especially with `longText`/blobs).

## 2024-05-15 - Missing Cache on Single Model Fetches
**Learning:** In read-heavy scenarios where the main page fetches articles from cache, the individual read views (e.g., `show()`) might still be hitting the database directly (`Article::findOrFail($id)`).
**Action:** When working on read-heavy models (like articles or blog posts), always cache the single model fetch using `Cache::remember("key_{$id}", ...)` and ensure the background command/job responsible for syncing the data correctly invalidates the specific key using `$model->wasChanged()` to clear `Cache::forget("key_{$id}")`.

## 2024-06-05 - Missing Indexes in updateOrCreate
**Learning:** The `updateOrCreate` method executes a SELECT query followed by an INSERT/UPDATE. When looping over external data (like a cron job fetching news), a missing index on the lookup column (e.g., `url`) results in an O(N*M) time complexity causing massive performance bottlenecks as the table grows.
**Action:** Always verify that lookup columns used in `updateOrCreate` or `firstOrCreate` have a database index.
