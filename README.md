# Full-Stack PHP + Vue Interview Task - Candidate CAND-LT8V

## Architecture
- Backend: PHP + SQLite (naive cache, optimized pagination, N+1 resolved)
- Frontend: Vue 3 + Pinia, fetch with abort controller, skeleton & offline cache
- Algorithm: 3-in-5 task, O(n log n) and streaming/bucket approach

## Backend Improvements
- Added indices on `created_at`
- Optimized pagination
- Resolved N+1 queries
- Added TTL-based caching

## Frontend Improvements
- Cancel in-flight fetches on filter change
- Show loading skeletons
- Offline cache of last successful fetch

## Benchmarks
- `bench/bench.csv` contains request times for API
- `alg/bench_alg.php` shows runtime comparison between O(n log n) and streaming algorithm

## Notes
- Token: CAND-LT8V
- Branch: candidate-CAND-LT8V
