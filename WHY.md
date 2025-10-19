# Key Decisions and Trade-offs

1. TTL-based caching vs event-driven: Chose TTL for simplicity and minimal ops complexity
2. Pagination optimized with index + LIMIT/OFFSET
3. N+1 resolved with single query JOIN instead of multiple per row
4. Frontend fetch cancellation and offline cache for UX responsiveness
5. Streaming algorithm added for big-data approach; O(n log n) used for correctness verification
