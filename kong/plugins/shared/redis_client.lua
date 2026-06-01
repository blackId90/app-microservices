local redis = require "resty.redis"

local M = {}

-- Connect to Redis with configuration
function M.connect(conf)
  local red = redis:new()
  red:set_timeout(conf.redis_timeout or 2000)

  local ok, err = red:connect(conf.redis_host, conf.redis_port)
  if not ok then
    kong.log.err("Failed to connect to Redis: ", err)
    return nil, err
  end

  -- Authenticate if password is provided
  if conf.redis_password then
    local res, err = red:auth(conf.redis_password)
    if not res then
      kong.log.err("Failed to authenticate Redis: ", err)
      return nil, err
    end
  end

  -- Select database if specified
  if conf.redis_database and conf.redis_database > 0 then
    local res, err = red:select(conf.redis_database)
    if not res then
      kong.log.err("Failed to select Redis database: ", err)
      return nil, err
    end
  end

  return red
end

-- Close Redis connection with keepalive
function M.close(red)
  if not red then return end

  local ok, err = red:set_keepalive(10000, 100)
  if not ok then
    kong.log.err("Failed to set Redis keepalive: ", err)
  end
end

-- Increment rate limit counter atomically
function M.increment_rate_limit(red, key, window_size)
  red:multi()
  red:incr(key)
  red:expire(key, window_size)

  local results, err = red:exec()
  if not results then
    return nil, err
  end

  -- results[1] is the INCR result (current count)
  return results[1]
end

-- Execute Redis operation with connection management
function M.execute(conf, operation)
  local red, err = M.connect(conf)
  if not red then
    return nil, err
  end

  local result, err = operation(red)
  M.close(red)

  return result, err
end

return M
