# Kong Multi-Tenant Gateway

## 📁 Project Structure

```
multi-tenant-gateway/
├── docker-compose.local.yml
├── config/
│   └── kong.yml
└── plugins/
    ├── shared/                           # Shared modules (DRY)
    │   ├── utils.lua                     # Common utilities
    │   ├── i18n.lua                      # Internationalization
    │   └── redis_client.lua              # Redis operations
    │
    ├── custom-inject-header/             # Inject Header
    │   ├── handler.lua                   # Main plugin logic
    │   └── schema.lua                    # Configuration schema
    │
    ├── custom-cors/                      # Header CORS plugin
    │   ├── handler.lua                   # Main plugin logic
    │   └── schema.lua                    # Configuration schema
    │
    ├── custom-rate-limiter/              # Rate limiter plugin
    │   ├── handler.lua                   # Main plugin logic
    │   └── schema.lua                    # Configuration schema
    │
    └── custom-error-handler/             # Error handler plugin
        ├── handler.lua                   # Main plugin logic
        └── schema.lua                    # Configuration schema
```

## 🎯 Key Improvements

### 1. **DRY Principle Applied**
- **Shared Modules**: Common functionality extracted to `plugins/shared/`
- **No Code Duplication**: JWT decoding, JSON helpers, error builders reused across plugins
- **Single Source of Truth**: Error messages, utilities centralized

### 2. **Clean Code Principles**

#### **Separation of Concerns**
- `utils.lua`: Generic utilities (JWT, JSON, metadata)
- `i18n.lua`: All translations in one place
- `redis_client.lua`: Redis operations isolated
- `handler.lua`: Pure business logic

#### **Single Responsibility**
Each function has one clear purpose:
- `get_rate_limit_identifier()`: Determine rate limit key
- `check_rate_limit()`: Check against Redis
- `handle_rate_limit_exceeded()`: Handle exceeded scenario

#### **Improved Readability**
- Descriptive function names
- Clear section separators
- Consistent code style
- Better error handling

### 3. **Maintainability**

#### **Easy to Test**
- Small, focused functions
- Clear inputs and outputs
- Minimal side effects

#### **Easy to Extend**
- Add new error types in `i18n.lua`
- Add new utilities in `utils.lua`
- Plugins remain thin and focused

## 🔧 Setup Instructions

### 1. Create Directory Structure

```bash
mkdir -p plugins/shared
mkdir -p plugins/custom-rate-limiter
mkdir -p plugins/custom-error-handler
mkdir -p config
```

### 2. Deploy Files

```bash
# Shared modules
plugins/shared/utils.lua
plugins/shared/i18n.lua
plugins/shared/redis_client.lua

# Rate limiter
plugins/custom-rate-limiter/handler.lua
plugins/custom-rate-limiter/schema.lua

# Error handler
plugins/custom-error-handler/handler.lua
plugins/custom-error-handler/schema.lua (existing)

# Configuration
config/kong.yml
docker-compose.local.yml
```

### 3. Start Services

```bash
docker compose -f docker-compose.local.yml up -d
```

### 4. Stop Services

```bash
docker compose -f docker-compose.local.yml down && docker image rm kong-kong:latest
```

### 5. Show Logs

```bash
docker logs -f multi-tenant-gateway
```

### 6. Verify Plugins

```bash
# Check Kong is running
curl http://localhost:8082

# Check plugin status
curl http://localhost:8082/status

# Check plugin status plugins
curl http://localhost:8082/plugins
```

## 📊 Plugin Execution Order

```
Request Flow:
1. custom-inject-header (PRIORITY: 100000)
   ├── Generate Unix key
   ├── Set request id and log id headers (in access and header_filter)
   └── Continue request

2. custom-rate-limiter (PRIORITY: 2000)
   ├── Check Redis rate limit
   ├── Set rate limit headers (in header_filter)
   └── Block if exceeded (429)

3. jwt (PRIORITY: 1005)
   ├── Validate JWT token
   └── Block if invalid (401/403)

4. response-transformer (PRIORITY: 800)
   ├── Set and remove headers (in access and header_filter)
   └── Continue request

5. custom-error-handler (PRIORITY: 900)
   ├── Intercept 401/403/429 errors
   ├── Translate error messages
   └── Format consistent response
```

## 🎨 Code Quality Features

### **Consistent Error Responses**
All errors follow the same format:
```json
{
  "status": 429,
  "message": "Too many requests. Please try again later.",
  "data": null,
  "error": {
    "request_id": "...",
    "status_code": 429,
    "code_name": "rate_limit_exceeded",
    "meta_request": { ... }
  }
}
```

### **i18n Support**
- Automatic language detection from `Accept-Language` header
- Support for English and Indonesian
- Easy to add new languages

### **Fail-Safe Design**
- Redis failures don't block requests (fail-open)
- Graceful error handling throughout
- Detailed logging for debugging

## 🔍 Benefits

| Before                              | After                               |
|-------------------------------------|-------------------------------------|
| Duplicated JWT decoding logic       | Single `utils.decode_jwt_payload()` |
| Repeated error message definitions  | Centralized `i18n.lua`              |
| Redis code in rate limiter only     | Reusable `redis_client.lua`         |
| Hard to maintain                    | Easy to extend and test             |
| ~500 lines total                    | ~350 lines with better organization |

## 📝 Configuration Example

```yaml
# config/kong.yml
plugins:
  - name: custom-rate-limiter
    config:
      enabled: true
      redis_host: redis-local
      limit_by_ip: 100
      limit_by_user: 1000
      window_size: 60

  - name: custom-error-handler
    config:
      enabled: true
```

## 🚀 Next Steps

1. **Add Tests**: Unit tests for shared modules
2. **Monitoring**: Add metrics collection
3. **Rate Limit Strategies**: Implement sliding window, token bucket
4. **Cache Layer**: Add local cache before Redis
5. **Admin API**: Custom endpoints for rate limit management
