local cjson = require "cjson.safe"
local socket = require "socket"
local M = {}

-- ============================================================================
-- JWT Functions
-- ============================================================================

-- Base64url decode
local function base64url_decode(input)
  input = input:gsub("-", "+"):gsub("_", "/")
  local pad = #input % 4
  if pad > 0 then
    input = input .. string.rep("=", 4 - pad)
  end
  return ngx.decode_base64(input)
end

-- Decode JWT payload tanpa validasi
function M.decode_jwt_payload(token)
  if not token then return nil end

  local payload_b64 = token:match("%.(.-)%.")
  if not payload_b64 then return nil end

  local ok, decoded = pcall(base64url_decode, payload_b64)
  if not ok or not decoded then return nil end

  local ok2, payload = pcall(cjson.decode, decoded)
  if not ok2 then return nil end

  return payload
end

-- Extract token from Authorization header
function M.extract_bearer_token(auth_header)
  if not auth_header then return nil end
  return auth_header:match("Bearer%s+(.+)")
end

-- ============================================================================
-- Language Detection
-- ============================================================================

function M.detect_lang()
  local lang_header = kong.request.get_header("Accept-Language")
  if not lang_header or #lang_header < 2 then
    return "en"
  end
  local prefix = lang_header:sub(1, 2):lower()
  return prefix == "id" and "id" or "en"
end

-- ============================================================================
-- JSON Helpers
-- ============================================================================

-- Safe JSON value converter
function M.json_string_or_null(val)
  if val == nil or val == "" then
    return "null"
  else
    return string.format("%q", val)
  end
end

-- Build query params as JSON with type casting
function M.build_query_params_json()
  local query_tbl = kong.request.get_query()
  local qp_obj = {}

  for k, v in pairs(query_tbl) do
    if type(v) == "table" then
      local arr = {}
      for _, subval in ipairs(v) do
        local num = tonumber(subval)
        table.insert(arr, num ~= nil and num or subval)
      end
      qp_obj[k] = arr
    else
      local num = tonumber(v)
      qp_obj[k] = num ~= nil and num or v
    end
  end

  return cjson.encode(qp_obj)
end

-- ============================================================================
-- Request Metadata
-- ============================================================================

function M.ensure_request_id_access()
  local req_id = ngx.ctx.request_id or kong.request.get_header("X-Request-ID")
  if not req_id then
    req_id = M.generate_request_id()
    kong.service.request.set_header("X-Request-ID", req_id)
    ngx.ctx.request_id = req_id
  end
  return req_id
end

function M.ensure_request_id_response()
  local req_id = ngx.ctx.request_id or kong.request.get_header("X-Request-ID")
  if not req_id then
    req_id = M.generate_request_id()
    ngx.ctx.request_id = req_id
  end
  kong.response.set_header("X-Request-ID", req_id)
  return req_id
end

function M.generate_request_id()
  local req_id = ngx.ctx.request_id
  if req_id then return req_id end

  return M.generate_uuid()
end

-- Generate timestamp
function M.generate_timestamp()
  local t = socket.gettime()
  local sec = math.floor(t)
  local usec = math.floor((t - sec) * 1e6)
  local date_str = os.date("%Y%m%d_%H%M%S", sec)
  local micro_str = string.format("%06d", usec)
  return date_str .. micro_str
end

-- Generate UUID
function M.generate_uuid()
  local random = math.random
  math.randomseed(ngx.now() * 1000 + ngx.worker.pid())

  return string.format(
    "%04x%04x-%04x-%04x-%04x-%04x%04x%04x",
    random(0, 0xffff), random(0, 0xffff),
    random(0, 0xffff),
    random(0, 0x0fff) + 0x4000,
    random(0, 0x3fff) + 0x8000,
    random(0, 0xffff), random(0, 0xffff), random(0, 0xffff)
  )
end

--- Generate profiling_start timestamp (epoch float dengan mikrodetik)
-- @return number (example: 1764561368.7199)
function M.generate_profiling_start()
  -- socket.gettime() menghasilkan float epoch (detik + mikrodetik)
  -- return socket.gettime()

  local t = socket.gettime()
  -- Format float dengan 6 digit setelah titik desimal
  return string.format("%.6f", t)
end

function M.now_iso8601()
  local now = ngx.now()
  local sec = math.floor(now)
  local msec = math.floor((now - sec) * 1000000)

  return os.date("!%Y-%m-%dT%H:%M:%S", sec) .. string.format(".%06dZ", msec)
end

-- Get user info from JWT token
function M.get_user_info(token)
  local payload = M.decode_jwt_payload(token)

  return {
    user_id = payload and payload.sub or nil,
    role_id = payload and payload.role or nil,
    jti = payload and payload.jti or nil
  }
end

-- Get request metadata
function M.get_request_metadata()
  return {
    method = kong.request.get_method(),
    host = kong.request.get_header("Host"),
    path = kong.request.get_path(),
    ip_address = kong.client.get_ip(),
    user_agent = kong.request.get_header("User-Agent")
  }
end

-- ============================================================================
-- Error Response Builder
-- ============================================================================

function M.build_error_response(status, code_name, message, token)
  local user_info = M.get_user_info(token)
  local request_meta = M.get_request_metadata()
  local query_json = M.build_query_params_json()

  local body = string.format([[
    {
      "status": %d,
      "message": %s,
      "data": null,
      "error": {
        "request_id": %s,
        "status_code": %d,
        "code_name": %s,
        "meta_request": {
          "method": %s,
          "host": %s,
          "path": %s,
          "query_params": %s,
          "route": null,
          "placeholders": [],
          "user_id": %s,
          "role_id": %s,
          "ip_address": %s,
          "user_agent": %s,
          "timestamp": %s
        }
      }
    }
    ]],
    status,
    M.json_string_or_null(message),
    M.json_string_or_null(M.generate_request_id()),
    status,
    M.json_string_or_null(code_name),
    M.json_string_or_null(request_meta.method),
    M.json_string_or_null(request_meta.host),
    M.json_string_or_null(request_meta.path),
    query_json,
    M.json_string_or_null(user_info.user_id),
    M.json_string_or_null(user_info.role_id),
    M.json_string_or_null(request_meta.ip_address),
    M.json_string_or_null(request_meta.user_agent),
    M.json_string_or_null(M.now_iso8601())
  )

  return body
end

return M
