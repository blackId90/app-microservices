local utils = require "kong.plugins.shared.utils"
local i18n = require "kong.plugins.shared.i18n"
local redis_client = require "kong.plugins.shared.redis_client"

local CustomRateLimiter = {
  PRIORITY = 2000,
  VERSION  = "1.0.0",
}

-- ============================================================================
-- Rate Limiter Logic
-- ============================================================================

local function get_rate_limit_identifier(conf)
  local auth_header = kong.request.get_header("Authorization")
  local token = utils.extract_bearer_token(auth_header)

  if token then
    local user_info = utils.get_user_info(token)
    if user_info.jti then
      return {
        key = "rate_limit:user:" .. user_info.jti,
        limit = conf.limit_by_user,
        type = "user"
      }
    end
  end

  -- Fallback to IP address
  return {
    key = "rate_limit:ip:" .. kong.client.get_ip(),
    limit = conf.limit_by_ip,
    type = "ip"
  }
end

local function check_rate_limit(conf, identifier)
  local current_count, err = redis_client.execute(conf, function(red)
    return redis_client.increment_rate_limit(red, identifier.key, conf.window_size)
  end)

  if not current_count then
    kong.log.err("Rate limiter failed: ", err)
    return nil -- Fail open
  end

  return current_count
end

local function save_rate_limit_context(identifier, current_count, conf)
  ngx.ctx.rate_limit_info = {
    limit = identifier.limit,
    remaining = math.max(0, identifier.limit - current_count),
    reset = ngx.time() + conf.window_size,
    identifier_type = identifier.type
  }
end

local function handle_rate_limit_exceeded(identifier, conf)
  local auth_header = kong.request.get_header("Authorization")
  local token = utils.extract_bearer_token(auth_header)
  local error_msg = i18n.get_error_message("rate_limit_exceeded")
  local body = utils.build_error_response(429, "rate_limit_exceeded", error_msg, token)

  return kong.response.exit(429, body, {
    ["Content-Type"] = "application/json; charset=utf-8",
    ["Retry-After"] = tostring(conf.window_size),
    ["X-RateLimit-Limit-" .. identifier.type] = tostring(identifier.limit),
    ["X-RateLimit-Remaining-" .. identifier.type] = "0",
    ["X-RateLimit-Reset-" .. identifier.type] = tostring(ngx.time() + conf.window_size)
  })
end

-- ============================================================================
-- Plugin Phases
-- ============================================================================

function CustomRateLimiter:access(conf)
  if not conf.enabled then
    return
  end

  local req_id = utils.ensure_request_id_access()
  local identifier = get_rate_limit_identifier(conf)
  local current_count = check_rate_limit(conf, identifier)

  if not current_count then
    return -- Fail open on error
  end

  save_rate_limit_context(identifier, current_count, conf)

  if current_count > identifier.limit then
    return handle_rate_limit_exceeded(identifier, conf)
  end
end

function CustomRateLimiter:header_filter(conf)
  local ctx = ngx.ctx
  if not ctx.rate_limit_info then
    return
  end

  local info = ctx.rate_limit_info
  kong.response.set_header("X-RateLimit-Limit-" .. info.identifier_type, tostring(info.limit))
  kong.response.set_header("X-RateLimit-Remaining-" .. info.identifier_type, tostring(info.remaining))
  kong.response.set_header("X-RateLimit-Reset-" .. info.identifier_type, tostring(info.reset))
end

return CustomRateLimiter
