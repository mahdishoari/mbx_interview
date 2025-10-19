# ADR: Cache Invalidation for /api/orders

- Candidate: CAND-LT8V
- Date: 2025-10-19

## Context
The `/api/orders` endpoint retrieves paginated user order data, often accessed repeatedly with similar parameters (`user_id`, `page`, `per_page`).  
Freshness requirement: near-real-time (within a few seconds is acceptable).  
Traffic pattern: many read requests, few writes (new orders or payment updates).

Goal: reduce redundant SQLite queries and improve latency without sacrificing correctness.

## Options Considered
1) TTL-based cache (per key)
2) Tag-based cache invalidation
3) Event-driven invalidation on write (pub/sub)
4) No cache (DB + indexes only)

## Decision
We choose **option (1): TTL-based cache** (simple read-through strategy).  
Each unique combination of `(user_id, page, per_page)` is cached for 120 seconds in a local file.  
Upon cache expiry or a write operation (order/payment update), the cache entry is refreshed.

## Rationale (Criteria)
- **Simplicity:** No external dependencies; file-based cache using `sys_get_temp_dir()`.
- **Staleness risk:** Acceptable within a 2-minute window.
- **DB offload:** Significantly fewer reads for identical queries.
- **Deployment complexity:** Minimal; portable with SQLite.
- **Failure modes:** Cache file corruption simply results in cache miss and DB fallback.

## When NOT to use this choice (Anti-case)
- High-frequency updates where 2-minute staleness is unacceptable.
- Multi-instance deployment needing shared cache (Redis or Memcached preferred).
- Scenarios requiring fine-grained invalidation by business event.

## Rollback Plan
Delete cache read/write logic and fallback to direct DB queries.  
Remove cache files from the temp directory (`sys_get_temp_dir()`).  
No schema or dependency changes are required.

## Implementation Notes
- **Key format:** `orders_{user_id}_{page}_{per_page}`
- **TTL:** 120 seconds
- **Storage:** Local filesystem cache (`/tmp` or `sys_get_temp_dir()`)
- **Invalidation triggers:**
    - Expiry time exceeded
    - New order or payment update for the same user  
