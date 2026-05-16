## 2024-05-14 - Selective Loading on Paginated Views
**Learning:** The `welcome()` method previously optimized queries by explicitly selecting columns to exclude the `longText` `content` field, but the same optimization was missed in the `index()` method, leading to unnecessary memory overhead when returning paginated lists of articles.
**Action:** Always check related list/index views when one query is optimized for column selection, as other queries returning the same model might also suffer from large unselected payload sizes (especially with `longText`/blobs).

## 2024-05-16 - Missing Index for updateOrCreate Lookups
**Learning:** The `FetchNewsCommand` uses `Article::updateOrCreate(['url' => ...])`. In Laravel, `updateOrCreate` or `firstOrCreate` perform a `SELECT` using the given attributes before deciding to insert or update. If the lookup column (e.g., `url`) lacks an index, this results in a full table scan for every single item processed in the loop, creating a massive backend bottleneck during background jobs or scraping.
**Action:** Always ensure that columns used as the unique identifier array in `updateOrCreate` or `firstOrCreate` are indexed.
