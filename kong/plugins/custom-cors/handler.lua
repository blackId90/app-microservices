local CustomCors = {
  PRIORITY = 99000,
  VERSION  = "1.0.0",
}

local function is_origin_allowed(origin, allowed_origins)
  if not allowed_origins or #allowed_origins == 0 then
    return true
  end

  for _, allowed in ipairs(allowed_origins) do
    if allowed == "*" then
      return true
    end
    if allowed == origin then
      return true
    end
  end

  return false
end

local function array_to_string(arr, delimiter)
  if not arr or #arr == 0 then
    return ""
  end
  return table.concat(arr, delimiter or ", ")
end

local function set_cors_headers(conf, origin)
  if conf.origins and #conf.origins > 0 then
    local is_allowed = is_origin_allowed(origin, conf.origins)
    if is_allowed then
      if conf.origins[1] == "*" then
        kong.response.set_header("Access-Control-Allow-Origin", "*")
      else
        kong.response.set_header("Access-Control-Allow-Origin", origin)
        kong.response.set_header("Vary", "Origin")
      end
    end
  else
    kong.response.set_header("Access-Control-Allow-Origin", origin or "*")
  end

  if conf.credentials then
    kong.response.set_header("Access-Control-Allow-Credentials", "true")
  end

  if conf.exposed_headers and #conf.exposed_headers > 0 then
    kong.response.set_header("Access-Control-Expose-Headers", array_to_string(conf.exposed_headers))
  end
end

local function handle_preflight(conf, origin)
  set_cors_headers(conf, origin)

  if conf.methods and #conf.methods > 0 then
    kong.response.set_header("Access-Control-Allow-Methods", array_to_string(conf.methods))
  end

  if conf.headers and #conf.headers > 0 then
    kong.response.set_header("Access-Control-Allow-Headers", array_to_string(conf.headers))
  else
    local request_headers = kong.request.get_header("Access-Control-Request-Headers")
    if request_headers then
      kong.response.set_header("Access-Control-Allow-Headers", request_headers)
    end
  end

  if conf.max_age then
    kong.response.set_header("Access-Control-Max-Age", tostring(conf.max_age))
  end

  if not conf.preflight_continue then
    return kong.response.exit(204, nil, {
      ["Content-Length"] = "0"
    })
  end
end

function CustomCors:access(conf)
  if not conf.enabled then
    return
  end

  local origin = kong.request.get_header("Origin")
  if not origin then
    return
  end

  if conf.origins and #conf.origins > 0 then
    local is_allowed = is_origin_allowed(origin, conf.origins)
    if not is_allowed then
      return
    end
  end

  local method = kong.request.get_method()
  if method == "OPTIONS" then
    return handle_preflight(conf, origin)
  end

  ngx.ctx.cors_origin = origin
end

function CustomCors:header_filter(conf)
  if not conf.enabled then
    return
  end

  local origin = ngx.ctx.cors_origin
  if not origin then
    return
  end

  set_cors_headers(conf, origin)
end

return CustomCors