local utils = require "kong.plugins.shared.utils"

local CustomInjectHeader = {
  PRIORITY = 100000,
  VERSION  = "1.0.0",
}

local function generate_uuid_counter()
  local worker_pid = ngx.worker.pid()
  local worker_id = ngx.worker.id() or 0
  local request_count = ngx.ctx.request_count or 0

  ngx.ctx.request_count = request_count + 1

  local uuid_part = utils.generate_uuid():sub(1, 24)

  return string.format("%s#%d-%d-%d", uuid_part, worker_id, worker_pid, request_count)
end

local function generate_tracker()
  local timestamp = ngx.now()
  local sec = math.floor(timestamp)
  local msec = math.floor((timestamp - sec) * 1000000)

  local worker_id = ngx.worker.id() or 0
  local worker_pid = ngx.worker.pid()

  local random = math.random
  math.randomseed(timestamp * 1000 + worker_pid)
  local rand_hex = string.format("%04x%04x", random(0, 0xffff), random(0, 0xffff))

  return string.format("%d%06d-%d-%d-%s", sec, msec, worker_id, worker_pid, rand_hex)
end

local function generate_header_value(generator)
  if generator == "uuid#counter" then
    return generate_uuid_counter()
  elseif generator == "tracker" then
    return generate_tracker()
  elseif generator == "timestamp" then
    return utils.generate_timestamp()
  elseif generator == "epoch" then
    return utils.generate_profiling_start()
  else
    return utils.generate_uuid()
  end
end

function CustomInjectHeader:access(conf)
  if not conf.enabled then
    return
  end

  if not ngx.ctx.injected_headers then
    ngx.ctx.injected_headers = {}
  end

  for _, header_config in ipairs(conf.headers) do
    local existing_header = kong.request.get_header(header_config.name)

    local header_value
    if existing_header and not header_config.override_existing then
      header_value = existing_header
    else
      header_value = generate_header_value(header_config.generator)
    end

    ngx.ctx.injected_headers[header_config.name] = {
      value = header_value,
      echo_downstream = header_config.echo_downstream
    }

    if header_config.name == "X-Request-ID" then
      ngx.ctx.request_id = header_value
    elseif header_config.name == "X-Log-ID" then
      ngx.ctx.log_id = header_value
    end

    kong.service.request.set_header(header_config.name, header_value)
  end
end

function CustomInjectHeader:header_filter(conf)
  if not conf.enabled then
    return
  end

  local ctx = ngx.ctx

  if not ctx.injected_headers then
    return
  end

  for header_name, header_data in pairs(ctx.injected_headers) do
    if header_data.echo_downstream then
      kong.response.set_header(header_name, header_data.value)
    else
      kong.response.clear_header(header_name)
    end
  end
end

return CustomInjectHeader
