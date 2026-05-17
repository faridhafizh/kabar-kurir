## 2024-05-14 - Selective Loading on Paginated Views
**Learning:** The `welcome()` method previously optimized queries by explicitly selecting columns to exclude the `longText` `content` field, but the same optimization was missed in the `index()` method, leading to unnecessary memory overhead when returning paginated lists of articles.
**Action:** Always check related list/index views when one query is optimized for column selection, as other queries returning the same model might also suffer from large unselected payload sizes (especially with `longText`/blobs).

## 2024-05-17 - Caching individual resources
**Learning:** For read-heavy applications like news or content where individual items don't frequently change, bypassing database calls entirely using caching on the `show` method yields substantial performance improvements with very low implementation cost.
**Action:** Always look out for standard `findOrFail` queries in controllers and wrap them in a `Cache::remember` if the model's update frequency is low and read frequency is high.
